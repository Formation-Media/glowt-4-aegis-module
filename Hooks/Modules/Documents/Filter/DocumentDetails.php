<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\VariantDocument;

class DocumentDetails
{
    public static function run(&$details, $module, $document)
    {
        $variant_document = VariantDocument::where('document_id', $document->id)->first();
        $project          = $variant_document->project_variant->project;
        $details          = array_merge(
            [
                'dictionary.reference' => $variant_document->reference,
                'dictionary.project'   => '<a href="/a/m/AEGIS/projects/project/'.$project->id.'">'.$project->title.'</a>',
                'dictionary.issue'     => $variant_document->issue,
                'dictionary.company'   => $project->company->name,
            ],
            $details
        );
        if ($document->category->prefix === 'FBL' && ($meta = $document->getMeta('feedback_list_type_id'))) {
            $feedback_list_type = FeedbackListType::find($meta);
            if ($feedback_list_type) {
                $details['aegis::phrases.feedback-list-type'] = $feedback_list_type->name;
            }
        }
    }
}
