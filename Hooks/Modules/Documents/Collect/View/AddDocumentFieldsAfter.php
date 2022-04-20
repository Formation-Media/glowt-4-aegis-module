<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use App\Helpers\Translations;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;

class AddDocumentFieldsAfter
{
    public static function run()
    {
        $companies           = Company::MDSS()->active()->pluck('name', 'id');
        $feedback_list_types = FeedbackListType::ordered()->pluck('reference', 'id')->toArray();
        $job_titles          = JobTitle::whereIn('id', (array) \Auth::user()->getMeta('aegis.discipline'))->formatted();
        $projects            = Project::ordered()->pluck('name', 'id')->toArray();
        $selected_variant    = null;
        $yes_no              = Translations::yes_no();
        if (isset($_GET['project_variant'])) {
            $selected_variant = ProjectVariant::find($_GET['project_variant']);
        }
        return view(
            'aegis::_hooks.add-document-fields-after',
            compact(
                'companies',
                'feedback_list_types',
                'job_titles',
                'projects',
                'selected_variant',
                'yes_no',
            )
        );
    }
}
