<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use App\Helpers\Translations;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\VariantDocument;

class DocumentFieldsAfter
{
    public static function run($document)
    {
        $companies           = Company::MDSS()->active()->ordered()->pluck('name', 'id');
        $feedback_list_types = FeedbackListType::ordered()->pluck('reference', 'id')->toArray();
        $job_titles          = JobTitle
                                    ::whereIn('id', (array) $document->created_by->getMeta('aegis.discipline'))
                                    ->ordered()
                                    ->formatted();
        $projects            = Project::ordered()->pluck('name', 'id')->toArray();
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
            'aegis::_hooks.add-document-fields-after',
            compact(
                'companies',
                'document',
                'document_variant',
                'feedback_list_types',
                'job_titles',
                'projects',
                'reference',
                'selected_variant',
                'yes_no',
            )
        );
    }
}
