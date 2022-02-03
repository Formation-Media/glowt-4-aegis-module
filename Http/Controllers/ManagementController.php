<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function import_errors(Request $request)
    {
        $error_file = 'modules/aegis/import/errors.json';
        $sections   = [];
        if (\Storage::exists($error_file)) {
            $sections = json_decode(\Storage::get($error_file), true);
        }
        $users = User
            ::with('metas')
            ->where('email', 'NOT LIKE', '%@formation%')
            ->where('email', 'NOT LIKE', 'jas-n@outlook.com')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        return parent::view(compact(
            'sections',
            'users'
        ));
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
