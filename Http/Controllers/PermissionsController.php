<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;

class PermissionsController
{
    public static function default(){
        $permissions=array(
            'companies'=>array(
                'add'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'company'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'delete'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'index'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>true),
                ),
            ),
            'management'=>array(
                'add-scope'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                ),
                'changelog'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'job-titles'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
                'types'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                ),
                'user-grades'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.hr.hr_manager')         =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.hr.competency_approver')=>array('has_permission'=>false,'fixed'=>false),
                ),
            ),
            'projects'=>array(
                'add'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'add-variant'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'project'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'delete'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'index'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
            ),
            'Scopes'=>array(
                'add'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'delete'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'index'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
                'scope'=>array(
                    \Config('roles.by_name.core.administrator')    =>array('has_permission'=>true, 'fixed'=>true),
                    \Config('roles.by_name.core.manager')          =>array('has_permission'=>true, 'fixed'=>false),
                    \Config('roles.by_name.core.staff')            =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.user')             =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.core.visitor')          =>array('has_permission'=>false,'fixed'=>false),
                    \Config('roles.by_name.aegis.project_manager')    =>array('has_permission'=>true, 'fixed'=>true),
                ),
            ),
        );
        return $permissions;
    }
}
