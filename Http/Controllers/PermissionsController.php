<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;

class PermissionsController
{
    public static function default(){
        $permissions=array(
            'companies'=>array(
                'add'=>array(
                    1=>array('has_permission'=>true, 'fixed'=>true),
                    2=>array('has_permission'=>true, 'fixed'=>false),
                    3=>array('has_permission'=>false,'fixed'=>false),
                    4=>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'company'=>array(
                    1=>array('has_permission'=>true, 'fixed'=>true),
                    2=>array('has_permission'=>true, 'fixed'=>false),
                    3=>array('has_permission'=>false,'fixed'=>false),
                    4=>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'delete'=>array(
                    1=>array('has_permission'=>true, 'fixed'=>true),
                    2=>array('has_permission'=>true, 'fixed'=>false),
                    3=>array('has_permission'=>false,'fixed'=>false),
                    4=>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'index'=>array(
                    1=>array('has_permission'=>true, 'fixed'=>true),
                    2=>array('has_permission'=>true, 'fixed'=>false),
                    3=>array('has_permission'=>false,'fixed'=>false),
                    4=>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>true),
                ),
            )
        );
        return $permissions;
    }
}
