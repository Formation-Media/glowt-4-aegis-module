<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\AEGIS\Models\CompetencyDetail;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\UserGrade;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\HR\Models\CompetencySection;

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
        );

        if (!$item->approval_process_item->approval_stage->next_stage() && $args['approved']) {
            // Everything's approved
            // Apply the author reference
            $author_company = Company::withTrashed()->find($document->meta['author_company']);
            $author_prefix  = $author_company->abbreviation.'-'.$document->created_by->getMeta('aegis.user-reference').'-';
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
        $user = $args['user'];
        if (!$aegis['user-reference']) {
            $i = 0;
            $name = substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1);
            $aegis['user-reference'] = $name . $i;
        }
        if (\DB::table('users_meta')->where([
                'key' => 'aegis.user-reference',
                'value' => $aegis['user-reference'],
            ])->count() >= 1) {
            return back()->withErrors(['message' => 'Reference already exists. Please try again.']);
        }
        $user->setMeta([
            'aegis.default-sections' => $aegis['default-sections'] ?? null,
            'aegis.discipline' => $aegis['discipline'] ?? null,
            'aegis.grade' => $aegis['grade'] ?? null,
            'aegis.type' => $aegis['type'],
            'aegis.user-reference' => $aegis['user-reference'],
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
