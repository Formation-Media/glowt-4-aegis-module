<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [];
    protected $table = 'm_aegis_types';

    public function projects(){
        return $this->hasMany(Project::class, 'type_id', 'id');
    }
}
