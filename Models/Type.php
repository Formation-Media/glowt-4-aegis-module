<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use Base_Model;

    protected $fillable = [
        'added_by',
        'name',
    ];
    protected $table = 'm_aegis_types';

    public function projects()
    {
        return $this->hasMany(Project::class, 'type_id', 'id');
    }
}
