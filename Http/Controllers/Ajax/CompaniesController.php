<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Helpers\Dates;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;

class CompaniesController extends Controller
{
    public function delete_company(Request $request)
    {
        $company = Company::findOrFail($request->id);
        $company->delete();
        return true;
    }
    public function get_reference(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array(
                'company_id' => 'required|exists:m_aegis_companies,id',
            )
        );
        if ($validator->fails()) {
            return parent::errors($validator);
        }
        $validated = $validator->validated();
        $prefix    = Company::find($validated['company_id'])->abbreviation;
        $next      = Project::where('reference', 'like', $prefix.'/%')->orderBy('id', 'desc')->first();
        if ($next) {
            $next = substr($next->reference, 4) + 1;
            while (Project::where('reference', $prefix.'/'.$next)->count()) {
                $next++;
            }
        } else {
            $next = '001';
        }
        return compact(
            'prefix',
            'next',
        );
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
                'dictionary.name' => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                'dictionary.abbreviation' => array(
                    'columns' => 'abbreviation',
                ),
                'dictionary.status' => array(
                    'columns'      => 'status',
                    'from_boolean' => array(
                        'Enabled',
                        'Disabled',
                    ),
                    'sortable' => true,
                ),
                'dictionary.added' => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => Dates::class,
                    'method'   => 'datetime',
                ),
                'dictionary.updated' => array(
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
                    'href'  => $this->link_base.'company/{{id}}',
                );
            }
        }
        return parent::to_ajax_table(
            Company::class,
            $row_structure,
            array(),
            function ($query) {
                return $query->withTrashed()->ordered();
            },
            function ($db, $processed, &$actions) use ($permissions) {
                if ($permissions['delete']) {
                    $group      = Company::withTrashed()->find($db['id']);
                    $is_deleted = $group->trashed();
                    $actions[]  = array(
                        'class' => $is_deleted ? 'js-restore-company' : 'js-delete-company',
                        'id'    => $group->id,
                        'style' => $is_deleted ? 'info' : 'danger',
                        'name'  => $is_deleted ? ___('dictionary.restore') : ___('dictionary.delete'),
                    );
                }
                return $processed;
            }
        );
    }
}
