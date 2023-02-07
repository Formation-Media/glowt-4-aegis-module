<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Composer;
use App\Helpers\Dates;
use App\Http\Controllers\Controller;
use App\Models\User;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;
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
        return redirect('a/management');
        if (is_formation() || is_dev()) {
            return parent::view();
        }
        return redirect('a/m/AEGIS/management/import-testing');
    }
    public function import_errors(Request $request)
    {
        return redirect('a/management');
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
    public function import_testing(Request $request)
    {
        return redirect('a/management');
        $errors      = [];
        $errors_file = 'modules/aegis/import/errors.json';
        $files       = [
            'modules/aegis/import/project_data.json',
            'modules/aegis/import/projects_and_document_signatures.json',
            'modules/aegis/import/projects_and_documents.json',
            'modules/aegis/import/projects.json',
        ];
        $projects = [];
        $tabs     = [];
        if (\Storage::exists($errors_file)) {
            $errors = json_decode(\Storage::get($errors_file), true);
            $tabs[] = 'Errors';
        }
        $this->errors = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        foreach ($files as $file) {
            if (\Storage::exists($file)) {
                $projects = json_decode(\Storage::get($file), true);
                $tabs[]   = 'Data';
                break;
            }
        }
        return parent::view(compact(
            'errors',
            'projects',
            'tabs'
        ));
    }
    public function job_titles(Request $request)
    {
        return parent::view();
    }
    public function project_management(Request $request)
    {
        $cache_location = 'project_management.json';
        $issue_count    = 0;
        $return         = [];
        $composer       = new Composer('aegis');
        $project_id     = 14660152;
        $session_key    = 'last-project-management-change';

        $last_change = session()->get($session_key);
        $project     = GitHub::repo()->projects()->show($project_id);

        list($org, $repo) = explode('/', $composer->getRepository());

        if (!$last_change || strtotime($project['updated_at']) > $last_change) {
            // Update cache
            if ($columns = GitHub::repo()->projects()->columns()->all($project_id)) {
                foreach ($columns as $column) {
                    if ($cards = GitHub::repo()->projects()->columns()->cards()->all(
                        $column['id'],
                        [
                            'per_page' => $column['name'] === 'Done' ? 0 : 9999,
                        ]
                    )) {
                        foreach ($cards as $card) {
                            if (array_key_exists('content_url', $card) && str_contains($card['content_url'], '/issues/')) {
                                $issue = last(explode('/', $card['content_url']));
                                $return[$column['name']][$issue] = [];
                                $issue_count ++;
                            }
                        }
                    }
                }
            }
            if ($issue_count > 0) {
                for ($i = 0; $i < ceil($issue_count / 100); $i++) {
                    $cards = GitHub::issue()->all(
                        $org,
                        $repo,
                        [
                            'page'     => $i + 1,
                            'per_page' => 100,
                            'state'    => 'open',
                        ]
                    );
                    foreach ($cards as $card) {
                        foreach ($return as $column => $issues) {
                            foreach ($issues as $issue_number => $issue) {
                                if ($card['number'] === $issue_number) {
                                    $return[$column][$card['number']] = $this->process_github_card($card);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if ($closed = GitHub::issue()->all(
                $org,
                $repo,
                [
                    'direction' => 'desc',
                    'per_page'  => config('settings.core.data.items_per_page'),
                    'sort'      => 'updated',
                    'state'     => 'closed',
                ]
            )) {
                foreach ($closed as $card) {
                    $return['Done'][$card['number']] = $this->process_github_card($card);
                }
            }
            // Save last update
            \Storage::put($cache_location, json_encode($return, JSON_PRETTY_PRINT));
            session()->put($session_key, strtotime($project['updated_at']));
        } else {
            $return = json_decode(
                \Storage::get($cache_location),
                true
            );
        }

        $columns = [];

        foreach ($return as $column => $issues) {
            $columns[$column] = array_filter($issues);
        }

        return parent::view([
            'columns' => array_filter($columns),
        ]);
    }
    public function project_type(Request $request, $id)
    {
        $breadcrumbs  = [];
        $companies    = Company::Ordered()->pluck('name', 'id')->toArray();
        $page_menu    = [];
        $project_type = Type::findOrFail($id);
        $parent_tree  = Type::where('id', '<>', $id)->getOrdered()->selectTree(select_parent: true);

        $breadcrumbs['m/AEGIS/management/project-types'] = 'aegis::phrases.project-types';
        if ($project_type->parents) {
            foreach ($project_type->parents as $id => $parent) {
                $breadcrumbs['m/AEGIS/management/project-types/'.$id] = $parent;
            }
        }
        $breadcrumbs[] = $project_type->name;

        if ($project_type->children()->count()) {
            $page_menu[] = [
                'href'  => '/a/m/AEGIS/management/project-types/'.$project_type->id,
                'icon'  => 'list-tree',
                'title' => 'dictionary.children',
            ];
        }

        return parent::render(compact(
            'breadcrumbs',
            'companies',
            'page_menu',
            'parent_tree',
            'project_type',
        ));
    }
    public function project_types(Request $request, $id = null)
    {
        $breadcrumbs  = [];
        $companies    = Company::Ordered()->pluck('name', 'id')->toArray();
        $project_type = Type::find($id);
        $parent_tree  = Type::getOrdered()->selectTree(select_parent: true);

        $page_menu = array(
            [
                'class' => 'js-add-type',
                'icon'  => 'plus',
                'title' => ['phrases.add', ['item' => 'dictionary.type']],
            ],
        );

        if ($project_type) {
            array_unshift($page_menu, [
                'href'  => '/a/m/AEGIS/management/project-type/'.$project_type->id,
                'icon'  => 'pen',
                'title' => 'dictionary.edit',
            ]);
            $breadcrumbs['m/AEGIS/management/project-types'] = 'aegis::phrases.project-types';
            if ($project_type->parents) {
                foreach ($project_type->parents as $id => $parent) {
                    $breadcrumbs['m/AEGIS/management/project-types/'.$id] = $parent;
                }
            }
            $breadcrumbs[] = $project_type->name;
        } else {
            $breadcrumbs[] = 'aegis::phrases.project-types';
        }

        return parent::view(compact(
            'breadcrumbs',
            'companies',
            'page_menu',
            'parent_tree',
            'project_type',
        ));
    }
    public function user_grades(Request $request)
    {
        return parent::view();
    }
    private function process_github_card($card)
    {
        $data = [
            'body'   => $card['body'],
            'date'   => Dates::datetime($card['created_at']),
            'id'     => $card['number'],
            'labels' => [],
            'link'   => $card['html_url'],
            'title'  => $card['title'],
        ];
        if ($card['labels']) {
            foreach ($card['labels'] as $label) {
                $data['labels'][$label['name']] = $label['color'];
            }
        }
        return $data;
    }
}
