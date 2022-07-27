<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Type;

class ManagementController extends Controller
{
    public function add_customer(Request $request)
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
        //         'title'       => ['phrases.add', ['item' => ___('aegis::phrases.feedback-list-type')]],
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
        $error_file         = 'modules/aegis/import/errors.json';
        $processed_sections = [];
        $section_input      = [];
        $sections           = [];
        if (\Storage::exists($error_file)) {
            $section_input = json_decode(\Storage::get($error_file), true);
        }
        foreach ($section_input as $section => $errors) {
            foreach ($errors as $reference => $message) {
                $explosion         = explode('/', $reference);
                if (count($explosion) > 1) {
                    $company           = array_shift($explosion);
                    $project_reference = array_shift($explosion);
                    $processed_sections[$section][$company][$project_reference][$message] = implode('/', $explosion);
                } else {
                    $processed_sections[$section]['Unknown']['Unknown'][$message] = $explosion[0];
                }
            }
        }
        foreach ($processed_sections as &$section) {
            ksort($section);
            foreach ($section as $company => &$projects) {
                ksort($projects);
            }
        }
        unset(
            $projects,
            $section
        );
        $sections = $processed_sections;
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
    public function project_type(Request $request, $id)
    {
        $project_type = Type::findOrFail($id);
        return parent::render(compact(
            'project_type'
        ));
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
