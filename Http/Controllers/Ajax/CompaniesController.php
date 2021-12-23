<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Helpers\Dates;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{
    // Ajax
    public function delete_company(Request $request)
    {
        $company = Company::findOrFail($request->id);
        $company->delete();
        return true;
    }
    public function restore_company(Request $request)
    {
        $company = Company::withTrashed()->find($request->id);
        $company->restore();
        return true;
    }
    public function table_companies()
    {
        $permissions   = \Auth::user()->feature_permissions('AEGIS', 'companies');
        $row_structure = array(
            'actions' => array(),
            'data'    => array(
                'id' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                __('dictionary.name') => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                __('dictionary.abbreviation') => array(
                    'columns' => 'abbreviation',
                ),
                __('dictionary.status') => array(
                    'columns'      => 'status',
                    'from_boolean' => array(
                        'Enabled',
                        'Disabled',
                    ),
                    'sortable' => true,
                ),
                __('dictionary.added') => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => Dates::class,
                    'method'   => 'datetime',
                ),
                __('dictionary.updated') => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => Dates::class,
                    'method'   => 'datetime',
                ),
            ),
        );
        if ($permissions) {
            if ($permissions['company']) {
                $row_structure['actions'][] = array(
                    'style' => 'primary',
                    'name'  => 'Edit',
                    'uri'   => $this->link_base.'company/{{id}}',
                );
            }
        }
        return parent::to_ajax_table(
            Company::class,
            $row_structure,
            array(),
            function ($query) {
                return $query->withTrashed()->orderBy('name');
            },
            function ($db, $processed, &$actions) use ($permissions) {
                if ($permissions['delete']) {
                    $group      = Company::withTrashed()->find($db['id']);
                    $is_deleted = $group->trashed();
                    $actions[]  = array(
                        'class' => $is_deleted ? 'js-restore-company' : 'js-delete-company',
                        'id'    => $group->id,
                        'style' => $is_deleted ? 'info' : 'danger',
                        'name'  => $is_deleted ? __('dictionary.restore') : __('dictionary.delete'),
                    );
                }
                return $processed;
            }
        );
    }
}
