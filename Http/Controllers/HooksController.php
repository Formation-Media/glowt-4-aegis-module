<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Helpers\Translations;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\AEGIS\Models\CompetencyDetail;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\UserGrade;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\HR\Models\CompetencySection;
use Modules\HR\Models\CompetencySubjectAchievement;

class HooksController extends AEGISController
{
    public static function chart_competencies_by_company()
    {
        return array(
            'method' => 'competencies_by_company',
            'module' => 'AEGIS',
            'title'  => ___('Competencies by Company'),
            'type'   => 'donut',
        );
    }
    public static function collect_add_user($args)
    {
        self::collect_store_user($args);
    }
    public static function collect_documents__view_approve_fields()
    {
        $companies  = Company::MDSS()->active()->ordered()->pluck('name', 'id');
        $job_titles = JobTitle::whereIn('id', (array) \Auth::user()->getMeta('aegis.discipline'))->ordered()->formatted();
        return view(
            'aegis::_hooks.approve-fields',
            compact(
                'companies',
                'job_titles',
            )
        );
    }
    public static function collect_documents__add_document($args)
    {
        if (isset($args['request']->aegis['project_variant'])) {
            $category = Category::find($args['request']->category);
            if ($category->prefix === 'FBL') {
                $args['new_document']->setMeta([
                    'feedback_list_type_id' => $args['request']->aegis['feedback-list-type'],
                    'final_feedback_list'   => $args['request']->aegis['final-feedback-list'],
                ]);
            }
            $args['new_document']->setMeta([
                'author_company' => $args['request']->aegis['author-company'],
                'author_role'    => $args['request']->aegis['author-role'],
            ]);
            $args['new_document']->save();
            $variant_document              = new VariantDocument();
            $variant_document->document_id = $args['new_document']->id;
            $variant_document->variant_id  = $args['request']->aegis['project_variant'];
            $project_variant               = ProjectVariant::find($args['request']->aegis['project_variant']);
            $variant_document->reference   = $project_variant->reference.'/'.$category->prefix
                                                .str_pad($args['request']->aegis['reference'], 2, '0', STR_PAD_LEFT);
            $issue                         = VariantDocument::where('reference', $variant_document->reference)->count();
            $variant_document->issue       = $issue + 1;
            $variant_document->save();
        }
    }
    public static function collect_documents__approve_deny($args)
    {
        $document         = $args['document'];
        $item             = $args['item'];
        $request          = $args['request'];
        $user             = $args['user'];
        $document_variant = VariantDocument
            ::with([
                'project_variant',
                'project_variant.project',
            ])
            ->firstWhere('document_id', $args['document']->id);

        DocumentApprovalItemDetails::updateOrInsert(
            ['approval_item_id' => $item->id],
            [
                'company_id'   => $document_variant->project_variant->project->company_id,
                'job_title_id' => $request->aegis['role'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        )->first();

        if (!$item->approval_process_item->approval_stage->next_stage() && $args['approved']) {
            // Everything's approved
            // Apply the author reference
            $author_company = Company::withTrashed()->find($document->meta['author_company']);
            $author_prefix  = $author_company->abbreviation.'-'.$user->getMeta('aegis.user-reference').'-';
            if ($previous_author_reference = DB
                ::table('m_documents_meta')
                ->where('key', 'author_reference')
                ->where('value', 'LIKE', $author_prefix.'%')
                ->orderByRaw('LENGTH(`value`) DESC')
                ->orderBy('value', 'desc')
                ->first()
            ) {
                $previous_author_reference = $previous_author_reference->value;
            }
            if ($previous_approval_reference = DocumentApprovalProcessItem
                ::where('reference', 'LIKE', $author_prefix.'%')
                ->orderByRaw('LENGTH(`reference`) DESC')
                ->orderBy('reference', 'desc')
                ->first()
            ) {
                $previous_approval_reference = $previous_approval_reference->reference;
            }
            if (!$previous_approval_reference && !$previous_author_reference) {
                $previous_reference = $author_prefix.'0';
            } else {
                $max = 0;
                foreach ([
                    'previous_author_reference',
                    'previous_approval_reference',
                ] as $key) {
                    if ($$key) {
                        $max = max($max, str_replace($author_prefix, '', $$key));
                    }
                }
                $previous_reference = $author_prefix.$max;
            }

            list($company_reference, $user, $increment) = explode('-', $previous_reference);
            $new_reference                              = implode('-', [$company_reference, $user, ++$increment]);
            $document->setMeta('author_reference', $new_reference);
            $document->save();

            // Loop through approval items and apply the signature reference
            $approval_process_items = DocumentApprovalProcessItem::where('document_id', $document->id);
            if ($approval_process_items->count()) {
                foreach ($approval_process_items->get() as $approval_process_item) {
                    $details        = DocumentApprovalItemDetails::where('approval_item_id', $approval_process_item->id)->first();
                    $user_reference = $approval_process_item->agent->getMeta('aegis.user-reference');

                    $user_prefix = $details->company->abbreviation.'-'.$user_reference.'-';

                    if ($previous_author_reference = DB
                        ::table('m_documents_meta')
                        ->where('key', 'author_reference')
                        ->where('value', 'LIKE', $user_prefix.'%')
                        ->orderByRaw('LENGTH(`value`) DESC')
                        ->orderBy('value', 'desc')
                        ->first()
                    ) {
                        $previous_author_reference = $previous_author_reference->value;
                    }
                    if ($previous_approval_reference = DocumentApprovalProcessItem
                        ::where('reference', 'LIKE', $user_prefix.'%')
                        ->orderByRaw('LENGTH(`reference`) DESC')
                        ->orderBy('reference', 'desc')
                        ->first()
                    ) {
                        $previous_approval_reference = $previous_approval_reference->reference;
                    }

                    if (!$previous_approval_reference && !$previous_author_reference) {
                        $previous_reference = $user_prefix.'0';
                    } else {
                        $max = 0;
                        foreach ([
                            'previous_author_reference',
                            'previous_approval_reference',
                        ] as $key) {
                            if ($$key) {
                                $max = max($max, str_replace($user_prefix, '', $$key));
                            }
                        }
                        $previous_reference = $user_prefix.$max;
                    }

                    list($company_reference, $user, $increment) = explode('-', $previous_reference);
                    $new_reference                              = implode('-', [$company_reference, $user, ++$increment]);
                    $approval_process_item->reference           = $new_reference;

                    $approval_process_item->save();
                }
            }
        }
        $item->save();
    }
    public static function collect_hr__add_competency($args)
    {
        if (isset($args['request']->aegis)) {
            $competency_company                = new CompetencyDetail;
            $competency_company->competency_id = $args['competency']->id;
            $competency_company->company_id    = $args['request']->aegis['company'];
            $competency_company->live_document = $args['request']->aegis['live-document'];
            $competency_company->save();
        }
        $default_sections = $args['competency']->user->getMeta('aegis.default-sections');
        if ($default_sections) {
            $competency_sections = CompetencySection
                ::whereIn('id', $default_sections)
                ->active()
                ->with([
                    'groups',
                    'groups.subjects',
                ])
                ->get();
            foreach ($competency_sections as $competency_section) {
                $groups = $competency_section->groups;
                if ($groups) {
                    foreach ($groups as $group) {
                        foreach ($group->subjects as $subject) {
                            CompetencySubjectAchievement::create([
                                'competency_id' => $args['competency']->id,
                                'subject_id'    => $subject->id,
                                'status'        => false,
                                'has_knowledge' => false,
                            ]);
                        }
                    }
                }
            }
        }
    }
    public static function collect_hr__edit_competency($args)
    {
        if (isset($args['request']->aegis)) {
            if ($cc = CompetencyDetail::where('competency_id', $args['competency']->id)->first()) {
                $cc->update([
                    'company_id'    => $args['request']->aegis['company'],
                    'live_document' => $args['request']->aegis['live-document'],
                ]);
            } else {
                $competency_company                = new CompetencyDetail;
                $competency_company->competency_id = $args['competency']->id;
                $competency_company->company_id    = $args['request']->aegis['company'];
                $competency_company->live_document = $args['request']->aegis['live-document'];
                $competency_company->save();
            }
        }
    }
    public static function collect_hr__view_add_competency_fields()
    {
        $company_data = Company::all();
        $companies    = array();
        $details      = [];
        if (count($company_data)) {
            foreach ($company_data as $company) {
                $companies[$company->id] = $company->name;
            }
        }
        $live_document = null;
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'companies',
                'details',
                'live_document'
            )
        );
    }
    public static function collect_hr__view_competency_fields($competency)
    {
        $company_data       = Company::all();
        $companies          = array();
        $competency_details = CompetencyDetail::where('competency_id', $competency->id)->first();
        $details            = [];
        $live_document      = $competency_details->live_document ?? $competency->user->getMeta('aegis.live-document');
        if ($live_document) {
            $details['aegis::phrases.live-document'] = '<a href="'.$live_document.'" target="_blank">'.___('dictionary.view').'</a>';
        }
        if (count($company_data)) {
            foreach ($company_data as $company) {
                $companies[$company->id] = $company->name;
            }
        }
        if ($bio = $competency->user->getMeta('hr.bio') ?? null) {
            $bio = nl2br($bio);
        }
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'bio',
                'competency',
                'competency_details',
                'companies',
                'details',
                'live_document',
            )
        );
    }
    public static function collect_hr__view_competency_summary($competency)
    {
        $competency_details = CompetencyDetail::firstWhere('competency_id', $competency->id);
        $details = [
            'dictionary.company' => $competency_details->company->name,
        ];
        $live_document = $competency_details->live_document ?? $competency->user->getMeta('aegis.live-document');
        if ($live_document) {
            $details['aegis::phrases.live-document'] = '<a href="'.$live_document.'" target="_blank">'.___('dictionary.view').'</a>';
        }
        if ($bio = $competency->user->getMeta('hr.bio') ?? null) {
            $bio = nl2br($bio);
        }
        return view(
            'aegis::_hooks.hr.competency-summary',
            compact(
                'bio',
                'details'
            )
        );
    }
    public static function collect_store_user($args)
    {
        $aegis = $args['request']->aegis;
        $user  = $args['user'];
        if (!$aegis['user-reference']) {
            $i                       = 0;
            $name                    = substr($user->first_name, 0, 1).substr($user->last_name, 0, 1);
            $aegis['user-reference'] = $name.$i;
            while (\DB::table('users_meta')->where([
                'key'   => 'aegis.user-reference',
                'value' => $aegis['user-reference'],
            ])->count()) {
                $aegis['user-reference'] = $name.$i++;
            }
        }
        $user->setMeta([
            'aegis.default-sections' => $aegis['default-sections'] ?? null,
            'aegis.discipline'       => $aegis['discipline'],
            'aegis.grade'            => $aegis['grade'] ?? null,
            'aegis.type'             => $aegis['type'],
            'aegis.user-reference'   => $aegis['user-reference'],
        ]);
        $user->save();
    }
    public static function collect_view_add_user()
    {
        return self::add_user_hook('add');
    }
    public static function collect_view_edit_profile($data)
    {
        return self::add_user_hook('profile', $data);
    }
    public static function collect_view_edit_user($data)
    {
        return self::add_user_hook('view', $data);
    }
    public static function collect_view_management()
    {
        return array(
            'https://docs.google.com/spreadsheets/d/1eLkArAaq6EjZlpqFrnoEaYgg36LUqO7XzwC7VaT8-bQ/edit#gid=0'
                => '- Formation Query Log',
            '/a/m/AEGIS/companies'                      => 'dictionary.companies',
            '/a/m/AEGIS/customers'                      => 'dictionary.customers',
            '/a/m/AEGIS/management/feedback-list-types' => 'aegis::phrases.feedback-list-types',
            '/a/m/AEGIS/management/import'              => 'dictionary.import',
            '/a/m/AEGIS/management/job-titles'          => 'aegis::phrases.job-titles',
            '/a/m/AEGIS/management/project-types'       => 'aegis::phrases.project-types',
            '/a/m/AEGIS/management/user-grades'         => 'aegis::phrases.user-grades',
        );
    }
    public static function collect_hr__view_set_up()
    {
        $permissions = \Auth::user()->feature_permissions('AEGIS', 'companies');
        return view('aegis::_hooks.hr.set-up-page', compact('permissions'));
    }
    public static function filter_documents__document_for_approval_details(&$data, $module, $approval_process_item)
    {
        $document_variant = VariantDocument
            ::where('document_id', $approval_process_item->document->id)
            ->first();
        $data['aegis::phrases.document-reference'] = $document_variant->reference;
        $data['aegis::phrases.project-number']     = $document_variant->project_variant->project->reference;
    }
    public static function filter_documents__document_approved_details(&$data, $module, $approval_process_item)
    {
        self::filter_documents__document_for_approval_details($data, $module, $approval_process_item);
    }
    public static function filter_documents__document_rejected_details(&$data, $module, $approval_process_item)
    {
        self::filter_documents__document_for_approval_details($data, $module, $approval_process_item);
    }
    public static function filter_documents__pdf_author_columns(&$data, $module, $document)
    {
        $data = [
            'documents::phrases.signatory-name'      => $data['documents::phrases.signatory-name'],
            'documents::phrases.signature-reference' => $document->meta['author_reference'],
            'documents::phrases.stage-name'          => ___('dictionary.author'),
            'dictionary.time'                        => $data['dictionary.time'],
            'dictionary.company'                     => Company::withTrashed()->find($document->meta['author_company'])->name,
            'dictionary.role'                        => JobTitle::find($document->meta['author_role'])->name,
        ];
    }
    public static function filter_documents__pdf_signature_columns(&$data, $module, $signature)
    {
        $details   = DocumentApprovalItemDetails::where('approval_item_id', $signature->id)->first();
        $company   = $details->company->name ?? null;
        $job_title = $details->job_title->name ?? null;
        if ($company) {
            $data['dictionary.company'] = $company;
        }
        if ($job_title) {
            $data['aegis::phrases.approved-as'] = $job_title;
        }
    }
    public static function filter_documents__pdf_signature_renderer(&$pdf)
    {
        $pdf = function ($pdf) {
            $author           = $pdf->document->created_by;
            $author_signature = File::where('hex_id', $author->getMeta('documents.signature'))->first();
            $company          = null;
            $document_meta    = $pdf->document->getMeta()->toArray();
            $items            = $pdf->document->document_approval_process_items->where('status', 'Approved');
            $job_title        = null;
            $signature_height = 50;
            $top_margin       = 10;
            $variant_document = VariantDocument::firstWhere('document_id', $pdf->document->id);

            if (isset($document_meta['author_company'])) {
                $company = Company::withTrashed()->find($document_meta['author_company'])->name;
            }
            if (isset($document_meta['author_role'])) {
                $job_title = JobTitle::find($document_meta['author_role'])->name;
            }

            $details = [
                'documents::phrases.signature-reference' => $document_meta['author_reference'] ?? null,
                'documents::phrases.signatory-name'      => $pdf->document->created_by->name,
                'aegis::phrases.job-title'               => $job_title,
                'dictionary.date'                        => $pdf->document->nice_datetime('updated_at'),
                'aegis::phrases.document-id'             => $variant_document->reference,
                'dictionary.issue'                       => $variant_document->issue,
            ];

            $pdf->ln($top_margin);
            $pdf->resetFillColor();

            if ($company) {
                $pdf->p(___('dictionary.for').' '.$company);
            }

            if (isset($author_signature)) {
                list($width, $height) = getimagesize(storage_path($author_signature->storage_path));
                $ratio = $height / $width;
                $pdf->Image('../storage'.$author_signature->storage_path, null, null, $signature_height, $signature_height * $ratio);
            }
            $pdf->columns($details, 1);

            if ($items) {
                foreach ($items as $item) {
                    $pdf->addPage();
                    $pdf->ln($top_margin);

                    $item_details = DocumentApprovalItemDetails::where('approval_item_id', $item->id)->first();
                    $signature    = File::where('hex_id', $item->agent->getMeta('documents.signature'))->first();

                    $company   = $item_details->company->name ?? null;
                    $job_title = $item_details->job_title->name ?? null;

                    $details = [
                        'documents::phrases.signature-reference' => $item->reference,
                        'documents::phrases.signatory-name'      => $item->agent->name,
                    ];

                    if ($signature) {
                        list($width, $height) = getimagesize(storage_path($signature->storage_path));
                        $ratio = $height / $width;
                        $pdf->Image('../storage'.$signature->storage_path, null, null, $signature_height, $signature_height * $ratio);
                    }

                    if ($job_title) {
                        $details['aegis::phrases.job-title'] = $job_title;
                    }
                    if ($company) {
                        $details['dictionary.company'] = $company;
                    }

                    $details['dictionary.date']            = $item->nice_datetime('updated_at');
                    $details['aegis::phrases.document-id'] = $variant_document->reference;
                    $details['dictionary.issue']           = $variant_document->issue;

                    $pdf->columns($details, 1);
                }
            }
        };
    }
    public static function filter_documents__reset_status($document, $module)
    {
        $document_variant = VariantDocument
            ::where('document_id', $document->id)
            ->first();
        $document_variant->issue++;
        $document_variant->save();
    }
    public static function filter_documents__pdf_signature_header(&$pdf, $module)
    {
        $variant_document = VariantDocument::where('document_id', $pdf->document->id)->first();
        $company          = $variant_document->project_variant->project->company;
        if ($company && $company->pdf_footer) {
            $file = storage_path($company->pdf_footer->storage_path);
            if (is_file($file)) {
                $pdf->setSourceFile($file);
                $temp = $pdf->importPage(1);
                $pdf->useTemplate($temp);
            }
        }
    }
    public static function filter_hr__ajax_table_competencies($args)
    {
        $ids     = [];
        $request = $args['request'];
        if ($request->filter) {
            if (isset($request->filter['user_status']) && $request->filter['user_status'] === '0') {
                $args['query']->orWhereIn('user_id', User::where('status', false)->pluck('id')->toArray());
            }
            if (isset($request->filter['company'])) {
                $companies = CompetencyDetail::select('competency_id')->where('company_id', $request->filter['company']);
                if ($companies->count()) {
                    $ids = array_merge($ids, array_column($companies->get()->toArray(), 'competency_id'));
                }
            }
        }
        if ($ids) {
            $args['query']->whereIn('m_hr_competencies.id', array_unique($ids));
        }
        return $args;
    }

    private static function add_user_hook($method, $user = null)
    {
        $competency_sections = false;
        $grades              = UserGrade::formatted();
        $job_titles          = JobTitle::formatted();
        $types               = self::user_types();
        if (Modules::isEnabled('HR')) {
            $competency_sections = CompetencySection::ordered()->pluck('name', 'id');
        }
        return view(
            'aegis::_hooks.add-user',
            compact(
                'competency_sections',
                'grades',
                'job_titles',
                'method',
                'types',
                'user',
            )
        )->render();
    }
}
