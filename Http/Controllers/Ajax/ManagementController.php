<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Notifications\Toast;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\CompanyType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\UserGrade;
use Modules\AEGIS\Models\Type;

class ManagementController extends Controller
{
    public function add_job_title(Request $request)
    {
        $grade         = new JobTitle();
        $grade->name   = $request->name;
        $grade->status = $request->status ?? 0;
        $grade->save();
        return $grade;
    }
    public function add_user_grade(Request $request)
    {
        $grade         = new UserGrade();
        $grade->name   = $request->name;
        $grade->status = $request->status ?? 0;
        $grade->save();
        return $grade;
    }
    public function add_type(Request $request)
    {
        return parent::validate(
            $request,
            [
                'company_ids' => 'required|array',
                'name'        => 'required',
                'parent_id'   => 'nullable|exists:m_aegis_types,id',
            ],
            function ($validated) {
                $type            = new Type();
                $type->name      = $validated['name'];
                $type->added_by  = \Auth::id();
                $type->parent_id = $validated['parent_id'];
                $type->save();
                foreach ($validated['company_ids'] as $company_id) {
                    CompanyType::create([
                        'company_id' => $company_id,
                        'type_id'    => $type->id,
                    ]);
                }
                return $type;
            },
        );
    }
    public function check_import_data(Request $request)
    {
        $percentage = $request->session()->get('aegis.import.percentage');
        $messages   = $request->session()->get('aegis.import.messages');
        $redirect   = $request->session()->get('aegis.import.redirect');
        $request->session()->forget('aegis.import.messages');
        $request->session()->forget('aegis.import.redirect');
        \Session::save();
        return compact(
            'messages',
            'percentage',
            'redirect',
        );
    }
    public function delete_job_title(Request $request)
    {
        $grade = JobTitle::findOrFail($request->id);
        $grade->delete();
        return true;
    }
    public function delete_user_grade(Request $request)
    {
        $grade = UserGrade::findOrFail($request->id);
        $grade->delete();
        return true;
    }
    public function delete_type(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $types = array();
            if ($types = Type::whereIn('id', $request->ids)->get()) {
                foreach ($types as $type) {
                    $names[] = $type->name;
                    $type->delete();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Delete Types',
                    'Successfully deleted '.number_format(count($names)).' types: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Delete Types', 'No types were selected for deletion.'));
        }
        return true;
    }
    public function disable_type(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($types = Type::whereIn('id', $request->ids)->get()) {
                foreach ($types as $type) {
                    $names[]      = $type->name;
                    $type->status = false;
                    $type->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Types',
                    'Successfully disabled '.number_format(count($names)).' types: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Disabled Types', 'No types were selected for disabling.'));
        }
        return true;
    }
    public function enable_type(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($types = Type::whereIn('id', $request->ids)->get()) {
                foreach ($types as $type) {
                    $names[]      = $type->name;
                    $type->status = false;
                    $type->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Types',
                    'Successfully disabled '.number_format(count($names)).' types: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast(
                'Disabled Types',
                'No types were selected for disabling.'
            ));
        }
        return true;
    }
    public function table_job_titles()
    {
        $permissions   = \Auth::user()->feature_permissions('AEGIS', 'companies');
        $row_structure = array(
            'actions' => array(),
            'data'    => array(
                'id' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                'Name' => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                'Status' => array(
                    'columns'      => 'status',
                    'from_boolean' => array(
                        'Enabled',
                        'Disabled',
                    ),
                    'sortable' => true,
                ),
                'Added' => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
                'Updated' => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
        );
        if ($permissions) {
            if ($permissions['delete']) {
                $row_structure['actions'][] = array(
                    'class' => 'js-delete-job-title',
                    'id'    => '{{id}}',
                    'style' => 'danger',
                    'name'  => 'Delete',
                );
            }
        }
        return parent::to_ajax_table('JobTitle', $row_structure, array(), function ($query) {
            return $query->ordered();
        });
    }
    public function table_user_grades()
    {
        $permissions   = \Auth::user()->feature_permissions('AEGIS', 'companies');
        $row_structure = array(
            'actions' => array(),
            'data' => array(
                'id' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                'Name' => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                'Status' => array(
                    'columns'      => 'status',
                    'from_boolean' => array(
                        'Enabled',
                        'Disabled',
                    ),
                    'sortable' => true,
                ),
                'Added' => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
                'Updated' => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            )
        );
        if ($permissions) {
            if ($permissions['delete']) {
                $row_structure['actions'][] = array(
                    'class' => 'js-delete-user-grade',
                    'id'    => '{{id}}',
                    'style' => 'danger',
                    'name'  => 'Delete',
                );
            }
        }
        return parent::to_ajax_table(
            'UserGrade',
            $row_structure,
            array(),
            function ($query) {
                return $query->ordered();
            }
        );
    }
}
