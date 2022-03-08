<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Helpers\Translations;
use Modules\AEGIS\Models\CompetencyCompany;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\UserGrade;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\HR\Models\CompetencySection;
use Modules\HR\Models\CompetencySkill;
use Modules\HR\Models\CompetencySubjectAchievement;
use Modules\HR\Models\CompetencySubjectAchievementSkill;

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
    public static function collect_documents__view_add_document_fields()
    {
        $feedback_list_types = FeedbackListType::orderBy('name')->pluck('reference', 'id')->toArray();
        $projects            = Project::orderBy('name')->pluck('name', 'id')->toArray();
        $project_variants    = null;
        $selected_variant    = null;
        $selected_project    = null;
        $yes_no              = Translations::yes_no();
        if (isset($_GET['project_variant'])) {
            $selected_variant = ProjectVariant::find($_GET['project_variant']);
            $selected_project = $selected_variant->project;
            $project_variants = $selected_project->variants->pluck('name', 'id')->toArray();
        }
        return view(
            'aegis::_hooks.add-document-fields',
            compact(
                'feedback_list_types',
                'projects',
                'project_variants',
                'selected_project',
                'selected_variant',
                'yes_no'
            )
        );
    }
    public static function collect_documents__view_approve_fields()
    {
        $companies  = Company::MDSS()->active()->pluck('name', 'id');
        $job_titles = JobTitle::whereIn('id', (array) \Auth::user()->getMeta('aegis.discipline'))->formatted();
        return view(
            'aegis::_hooks.approve-fields',
            compact(
                'companies',
                'job_titles',
            )
        );
    }
    public static function collect_documents__view_document_fields($document)
    {
        $feedback_list_types = FeedbackListType::orderBy('name')->pluck('reference', 'id')->toArray();
        $projects            = Project::orderBy('name')->pluck('name', 'id')->toArray();
        $document_variant    = VariantDocument::where('document_id', $document->id)->first();
        $yes_no              = Translations::yes_no();
        if ($document_variant) {
            $selected_variant = $document_variant->project_variant;
            $selected_project = $document_variant->project_variant->project;
            $project_variants = $selected_project->variants->pluck('name', 'id')->toArray();
            $reference        = $document_variant->reference;
        } else {
            $selected_variant = null;
            $selected_project = null;
            $project_variants = [];
            $reference        = null;
        }
        return view(
            'aegis::_hooks.add-document-fields',
            compact(
                'document',
                'feedback_list_types',
                'projects',
                'project_variants',
                'reference',
                'selected_project',
                'selected_variant',
                'yes_no',
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
                $args['new_document']->save();
            }
            $variant_document              = new VariantDocument();
            $variant_document->document_id = $args['new_document']->id;
            $variant_document->variant_id  = $args['request']->aegis['project_variant'];
            $project_variant               = ProjectVariant::find($args['request']->aegis['project_variant']);
            $variant_document->reference   = $project_variant->project->reference.'/'.$category->prefix
                                                .str_pad($args['request']->aegis['reference'], 2, '0', STR_PAD_LEFT);
            $issue                         = VariantDocument::where('reference', $variant_document->reference)->count();
            $variant_document->issue       = $issue + 1;
            $variant_document->save();
        }
    }
    public static function collect_documents__approve_deny($args)
    {
        $document = $args['document'];
        $item     = $args['item'];
        $request  = $args['request'];
        $user     = $args['user'];

        DocumentApprovalItemDetails::updateOrInsert(
            ['approval_item_id' => $item->id],
            [
                'company_id'   => $request->aegis['company'],
                'job_title_id' => $request->aegis['role'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        );

        if (!$item->approval_process_item->approval_stage->next_stage() && $args['approved']) { # Everything's approved
            // Loop through approval items and apply the signature reference
            $approval_process_items = DocumentApprovalProcessItem::where('document_id', $document->id);
            if ($approval_process_items->count()) {
                foreach ($approval_process_items->get() as $approval_process_item) {
                    $details        = DocumentApprovalItemDetails::where('approval_item_id', $approval_process_item->id)->first();
                    $user_reference = $approval_process_item->agent->getMeta('aegis.user-reference');

                    $previous_reference = DocumentApprovalProcessItem
                        ::where('reference', 'LIKE', $details->company->abbreviation.'-'.$user_reference.'-%')
                        ->orderBy('reference', 'desc')
                        ->first();
                    if ($previous_reference) {
                        $previous_reference = $previous_reference->reference;
                    } else {
                        $previous_reference = $details->company->abbreviation.'-'.$user_reference.'-0';
                    }

                    list($company_reference, $user, $increment) = explode('-', $previous_reference);

                    $approval_process_item->reference = implode('-', [$company_reference, $user, $increment + 1]);

                    $approval_process_item->save();
                }
            }
        }

        $item->save();
    }
    public static function collect_documents__edit_document($args)
    {
        if (isset($args['request']->aegis['project_variant'])) {
            VariantDocument::updateOrCreate(
                ['document_id' => $args['document']->id],
                ['variant_id' => $args['request']->aegis['project_variant']]
            );
        }
    }
    public static function collect_hr__add_competency($args)
    {
        if (isset($args['request']->aegis)) {
            $competency_company                = new CompetencyCompany;
            $competency_company->competency_id = $args['competency']->id;
            $competency_company->company_id    = $args['request']->aegis['company'];
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
            if ($cc = CompetencyCompany::where('competency_id', $args['competency']->id)->first()) {
                $cc->update([
                    'company_id' => $args['request']->aegis['company'],
                ]);
            } else {
                $competency_company                = new CompetencyCompany;
                $competency_company->competency_id = $args['competency']->id;
                $competency_company->company_id    = $args['request']->aegis['company'];
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
        $live_document = \Auth::user()->getMeta('aegis.live-document');
        if ($live_document) {
            $details[___('aegis::phrases.live-document')] = '<a href="'.$live_document.'" target="_blank">'
                .___('dictionary.view').'</a>';
        }
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'companies',
                'details',
            )
        );
    }
    public static function collect_hr__view_competency_fields($competency)
    {
        $company_data  = Company::all();
        $companies     = array();
        $details       = [];
        $live_document = $competency->user->getMeta('aegis.live-document');
        if ($live_document) {
            $details[___('aegis::phrases.live-document')] = '<a href="'.$live_document.'" target="_blank">'
                .___('dictionary.view').'</a>';
        }
        $value = CompetencyCompany::where('competency_id', $competency->id)->first();
        if ($value) {
            $value = $value->company_id;
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
                'companies',
                'details',
                'value'
            )
        );
    }
    public static function collect_hr__view_competency_summary($competency)
    {
        $details       = [];
        $live_document = $competency->user->getMeta('aegis.live-document');
        if ($live_document) {
            $details[___('aegis::phrases.live-document')] = '<a href="'.$live_document.'" target="_blank">'
                .___('dictionary.view').'</a>';
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
            'aegis.live-document'    => $aegis['live-document'] ?? null,
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
    public static function collect_dashboard_charts()
    {
        $dashboard_charts = array();
        if (\Auth::user()->has_role('HR::HR Manager')) {
            $dashboard_charts['Competencies by Company'] = array(
                'method' => 'chart_competencies_by_company',
            );
        }
        return $dashboard_charts;
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
    public static function collect_view_table_filter($args)
    {
        $compact = [
            'args'
        ];
        if ($args['module'] == 'HR' && $args['method'] == 'table_competencies') {
            $companies = array();
            $company   = isset($args['request']['filter']['company']) ? $args['request']['filter']['company'] : false;

            if ($competency_companies = Company::all()) {
                foreach ($competency_companies as $company) {
                    $companies[$company->id] = $company->name;
                }
            }

            $compact = array_merge(
                $compact,
                [
                    'args',
                    'companies',
                    'company',
                ]
            );
        }
        return view(
            'aegis::_hooks.table-filter',
            compact(...$compact)
        )->render();
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
        $data['aegis::phrases.project-reference']  = $document_variant->project_variant->project->reference;
    }
    public static function filter_documents__document_approved_details(&$data, $module, $approval_process_item)
    {
        self::filter_documents__document_for_approval_details($data, $module, $approval_process_item);
    }
    public static function filter_documents__document_rejected_details(&$data, $module, $approval_process_item)
    {
        self::filter_documents__document_for_approval_details($data, $module, $approval_process_item);
    }
    public static function filter_documents__pdf_signature_columns(&$data, $module, $signature)
    {
        $details   = DocumentApprovalItemDetails::where('approval_item_id', $signature->id)->first();
        $company   = $details->company->name ?? null;
        $job_title = $details->job_title->name ?? null;
        if ($company) {
            $data[___('dictionary.company')] = $company;
        }
        if ($job_title) {
            $data[___('aegis::phrases.approved-as')] = $job_title;
        }
    }
    // public static function filter_card_view_filter(&$query, $module, $request)
    // {
    //     if (isset($request->module)
    //         && $request->module === 'Documents'
    //         && $request->model === 'Document'
    //     ) {
    //         $query
    //             ->join('m_aegis_variant_documents', 'm_aegis_variant_documents.document_id', 'm_documents_documents.id')
    //             ->select([
    //                 'm_documents_documents.*',
    //                 'm_aegis_variant_documents.reference',
    //             ])
    //             ->where('m_documents_documents.category_id', $request->id)
    //             ->groupBy('m_documents_documents.id');
    //     }
    // }
    public static function filter_card_view_result(&$data, $module)
    {
        if (isset($data['request']->module)
            && $data['request']->module === 'Documents'
            && $data['request']->model === 'Document'
        ) {
            $additional_details = [];
            if ($data['result']->reference) {
                $additional_details[] = [
                    [
                        'icon'  => 'hashtag',
                        'label' => 'dictionary.reference',
                        'value' => $data['result']->reference,
                    ],
                ];
            }
            $data['details'] = array_merge(
                $additional_details,
                $data['details']
            );
        }
    }
    // public static function filter_card_view_search(&$search_columns, $module, $request)
    // {
    //     if (isset($request->module)
    //         && $request->module === 'Documents'
    //         && $request->model === 'Document'
    //     ) {
    //         $search_columns = array_merge(
    //             [
    //                 'm_aegis_variant_documents.reference'
    //             ],
    //             $search_columns
    //         );
    //     }
    // }
    public static function filter_documents__document_details(&$details, $module, $document)
    {
        $variant_document                = VariantDocument::where('document_id', $document->id)->first();
        $project                         = $variant_document->project_variant->project;
        $details['dictionary.reference'] = $variant_document->reference;
        $details['dictionary.project']   = '<a href="/a/m/AEGIS/projects/project/'.$project->id.'">'.$project->title.'</a>';
        if ($document->category->prefix === 'FBL' && ($meta = $document->getMeta('feedback_list_type_id'))) {
            $feedback_list_type = FeedbackListType::find($meta);
            if ($feedback_list_type) {
                $details['aegis::phrases.feedback-list-type'] = $feedback_list_type->name;
            }
        }
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
        $request = $args['request'];
        if ($request->filter) {
            if (isset($request->filter['company'])) {
                $companies = CompetencyCompany::select('competency_id')->where('company_id', $request->filter['company']);
                if ($companies->count()) {
                    $ids = array_column($companies->get()->toArray(), 'competency_id');
                    $args['query']->whereIn('m_hr_competencies.id', $ids);
                }
            }
        }
        return $args;
    }

    public static function filter_main_menu(&$menu, $module)
    {
        if (Modules::isEnabled('Documents')) {
            foreach ($menu as &$item) {
                if ($item['title'] === 'dictionary.documents') {
                    $item['title'] = 'MDSS';
                }
            }
            $menu[] = array(
                'icon'  => 'folder',
                'link'  => '/a/m/'.$module->getName().'/projects',
                'title' => 'dictionary.projects',
            );
        }
    }

    private static function add_user_hook($method, $user = null)
    {
        $competency_sections = false;
        $grades              = UserGrade::formatted();
        $job_titles          = JobTitle::formatted();
        $types               = self::user_types();
        if (Modules::isEnabled('HR')) {
            $competency_sections = CompetencySection::orderBy('name')->pluck('name', 'id');
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
