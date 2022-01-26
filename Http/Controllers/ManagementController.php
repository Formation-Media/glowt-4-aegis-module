<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    public function add_scope(Request $request)
    {
        return parent::view();
    }
    public function feedback_list_types(Request $request)
    {
        // $page_menu = [
        //     [
        //         'd-bs-toggle' => 'modal',
        //         'd-bs-target' => '#modal-add-feedback-list-type',
        //         'icon'        => 'plus',
        //         'title'       => ['phrases.add', ['item' => __('aegis::phrases.feedback-list-type')]],
        //     ],
        // ];
        return parent::view(/*compact('page_menu')*/);
    }
    public function import(Request $request)
    {
        if (is_formation() || is_dev()) {
            return parent::view();
        }
        abort(401);
    }
    public function job_titles(Request $request)
    {
        return parent::view();
    }
    public function project_types(Request $request)
    {
        return parent::view();
    }
    public function user_grades(Request $request)
    {
        return parent::view();
    }
}
