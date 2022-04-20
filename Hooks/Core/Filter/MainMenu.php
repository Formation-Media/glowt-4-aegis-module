<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use App\Helpers\Modules;

class MainMenu
{
    public static function run(&$menu, $module)
    {
        if (Modules::isEnabled('Documents')) {
            $menu[] = array(
                'icon'  => 'folder',
                'link'  => '/a/m/'.$module->getName().'/projects',
                'title' => 'dictionary.projects',
            );
        }
    }
}
