<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Toast;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{
    public function add_scope($request)
    {
        return Scope::create([
            'name'     => $request->name,
            'added_by' => \Auth::id(),
        ]);
    }
    public function autocomplete_scopes($request)
    {
        $return = array();
        if ($scopes = Scope::search(
            array(
                'name'
            ),
            '%'.$request->term.'%'
        )->paged()) {
            foreach ($scopes as $scope) {
                $return[] = array(
                    'data'    => $scope,
                    'value'   => $scope->id,
                    'content' => $scope->name,
                );
            }
        }
        return $return;
    }
    public function delete_scope(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $scopes = array();
            if ($scopes = Scope::whereIn('id', $request->ids)->get()) {
                foreach ($scopes as $scope) {
                    $names[] = $scope->name;
                    $scope->delete();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Delete Scopes',
                    'Successfully deleted '.number_format(count($names)).' scopes: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast(
                'Delete Scopes',
                'No scopes were selected for deletion.'
            ));
        }
        return true;
    }
    public function disable_scope(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($scopes = Scope::whereIn('id', $request->ids)->get()) {
                foreach ($scopes as $scope) {
                    $names[]       = $scope->name;
                    $scope->status = false;
                    $scope->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Scopes',
                    'Successfully disabled '.number_format(count($names)).' scopes: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Disabled Scopes', 'No Scopes were selected for disabling.'));
        }
        return true;
    }
    public function enable_scope(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($scopes = Scope::whereIn('id', $request->ids)->get()) {
                foreach ($scopes as $scope) {
                    $names[]       = $scope->name;
                    $scope->status = false;
                    $scope->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Scopes',
                    'Successfully disabled '.number_format(count($names)).' scopes: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Disabled Scopes', 'No scopes were selected for disabling.'));
        }
        return true;
    }

    public function table_view($request)
    {
        $user           = \Auth::user();
        $global_actions = array();
        $actions        = array(
            array(
                'style' => 'primary',
                'name'  => __('View'),
                'uri'   => '/a/m/AEGIS/scopes/scope/{{id}}',
            ),
        );
        if ($user->has_role('core::Administrator') || $user->has_role('core::Manager')) {
            $global_actions = array(
                array(
                    'action' => 'enable-scope',
                    'icon'   => 'square-check',
                    'style'  => 'success',
                    'title'  => __('dictionary.enable'),
                ),
                array(
                    'action' => 'disable-scope',
                    'icon'   => 'square-xmark',
                    'style'  => 'warning',
                    'title'  => __('dictionary.disable'),
                ),
                array(
                    'action' => 'delete-scope',
                    'icon'   => 'square-xmark',
                    'style'  => 'danger',
                    'title'  => __('dictionary.delete'),
                ),
            );
        }
        $row_structure = array(
            'actions' => $actions,
            'data'    => array(
                'ID' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                __('dictionary.name') => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                __('dictionary.reference') => array(
                    'columns'      => 'reference',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                __('phrases.added-by') => array(
                    'sortable' => true,
                ),
                __('phrases.added-at') => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
                __('phrases.updated-at') => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
            'status' => true
        );
        return parent::to_ajax_table(
            'Scope',
            $row_structure,
            $global_actions,
            function ($query) {
                return $query;
            },
            function ($in, $out) {
                $scope                       = Scope::where('id', $in['id'])->first();
                $added_by                    = User::where('id', $scope->added_by)->first();
                $out[__('phrases.added-by')] = $added_by->name;
                return $out;
            }
        );
    }
}
