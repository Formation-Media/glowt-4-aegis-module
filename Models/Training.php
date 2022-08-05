<?php

namespace Modules\AEGIS\Models;

use App\Helpers\Dates;
use App\Helpers\FontAwesome;
use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\AEGIS\Helpers\Icons;
use Modules\AEGIS\Helpers\Training as TrainingHelper;

class Training extends Model
{
    use Base_Model;

    protected $fillable = [
        'customer_id',
        'description',
        'duration_length',
        'duration_period',
        'end_date',
        'location',
        'name',
        'presentation',
        'presenter_id',
        'reference',
        'start_date',
    ];
    protected $table = 'm_aegis_training';

    public function dateRange(): Attribute
    {
        return new Attribute(
            get: fn () => Dates::date($this->start_date).' - '.Dates::date($this->end_date),
        );
    }
    public function details(): Attribute
    {
        return new Attribute(
            get: fn () => [
                'dictionary.added' => [
                    'icon'  => FontAwesome::datetime_icon((string) $this->created_at, true),
                    'value' => $this->created_by->name,
                ],
                'phrases.added-by' => [
                    'icon'  => Icons::user(),
                    'value' => $this->created_by->name,
                ],
                'dictionary.updated' => [
                    'icon'  => FontAwesome::datetime_icon((string) $this->created_at, true),
                    'value' => $this->created_by->name,
                ],
                'phrases.updated-by' => [
                    'icon'  => Icons::user(),
                    'value' => $this->updated_by->name,
                ],
            ]
        );
    }
    public function duration(): Attribute
    {
        return new Attribute(
            get: fn () => $this->duration_length.' '.TrainingHelper::periods($this->duration_period),
        );
    }
    public function title(): Attribute
    {
        return new Attribute(
            get: fn () => $this->reference.': '.$this->name,
        );
    }
    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
