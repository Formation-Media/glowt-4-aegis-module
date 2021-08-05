<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scope extends Model
{
    use \App\Models\Base_Model;
    use SoftDeletes;
    protected $fillable = [];
    protected $table = 'm_aegis_Scope';

    public function projects()
    {
        return $this->hasMany(Project::class, 'scope_id', 'id');
    }
}
