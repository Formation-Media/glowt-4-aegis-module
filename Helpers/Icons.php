<?php

namespace Modules\AEGIS\Helpers;

class Icons
{
    public static function get($type)
    {
        $type  = \Str::singular($type);
        $types = [
            'company'  => 'buildings',
            'customer' => 'building',

            'date'        => 'calendar-day',
            'description' => 'align-justify',
            'duration'    => 'timer',

            'issue' => 'hashtag',

            'project' => 'folder',

            'template' => 'memo-circle-check',
            'training' => 'chalkboard-user',

            'user' => 'user',
        ];
        if ($type === null) {
            return $types;
        } elseif (array_key_exists($type, $types)) {
            return $types[$type];
        }
        return 'question';
    }
    public static function __callStatic($name, $arguments)
    {
        return self::get(\Str::slug($name));
    }
}
