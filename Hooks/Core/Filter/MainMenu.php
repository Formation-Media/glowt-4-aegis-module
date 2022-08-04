<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use App\Helpers\Modules;
use Modules\AEGIS\Helpers\Icons;

class MainMenu
{
    public static function run(&$menu, $module)
    {
        if (Modules::isEnabled('Documents')) {
            $menu[] = array(
                'icon'  => Icons::projects(),
                'link'  => '/a/m/'.$module->getName().'/projects',
                'title' => 'dictionary.projects',
            );
            foreach ($menu as $i => &$item) {
                if (in_array(
                    $item['link'],
                    [
                        '/a/m/AEGIS/projects',
                        '/a/m/Documents/templates',
                    ]
                )) {
                    unset($menu[$i]);
                } elseif ($item['link'] === '/a/m/Documents/document') {
                    $item['title'] = 'MDSS';
                }
            }
        }
    }
}
