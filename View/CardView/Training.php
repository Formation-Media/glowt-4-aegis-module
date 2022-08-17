<?php

namespace Modules\AEGIS\View\CardView;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Icons;

class Training
{
    public function actions($result, Request $request, $row)
    {
        return [
            [
                'href'  => '/a/m/AEGIS/training/training/'.$result->id,
                'name'  => 'dictionary.edit',
            ],
        ];
    }
    public function details($result, Request $request, $row)
    {
        return [
            'dictionary.customer' => [
                'icon'  => Icons::customer(),
                'value' => $result->customer->name,
            ],
            'dictionary.description' => [
                'icon'  => Icons::description(),
                'value' => strip_tags($result->description),
            ],
            'aegis::dictionary.dates' => [
                'icon'  => Icons::date(),
                'value' => $result->date_range,
            ],
            'dictionary.duration' => [
                'icon'  => Icons::duration(),
                'value' => $result->duration,
            ],
        ];
    }
    public function filter(Builder $query, Request $request)
    {
        return $query->orderByRaw('end_date < DATE(NOW())')->orderBy('start_date');
    }
    public function progress($result)
    {
        $before_start_date = (strtotime($result->start_date) - strtotime(date('Y-m-d'))) / 60 / 60 / 24;
        if ($before_start_date < 0) {
            $before_end_date = (strtotime($result->end_date) - strtotime(date('Y-m-d'))) / 60 / 60 / 24;
            if ($before_end_date < 0) {
                $color = 'dark';
            } else {
                $color = 'success';
            }
        } elseif ($before_start_date === 0) {
            $color = 'success';
        } elseif ($before_start_date <= 7) {
            $color = 'warning';
        } elseif ($before_start_date <= 30) {
            $color = 'info';
        } else {
            $color = 'light';
        }
        return array(
            'color' => $color,
            'value' => 100 - (min($before_start_date, 30) / 30 * 100),
        );
        return $result->id % 100;
    }
    public function reference($result, Request $request, $row)
    {
        return $result->reference;
    }
    public function search()
    {
        return [
            'description',
            'location',
            'name',
            'presentation',
            'reference',
        ];
    }
    public function sort()
    {
        return [
            'name' => [
                'translation' => 'dictionary.name',
            ],
            'reference' => [
                'translation' => 'dictionary.reference',
            ],
            'start_date' => [
                'translation' => 'aegis::phrases.start-date',
            ],
        ];
    }
    public function title($result)
    {
        return $result->name;
    }
}
