<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scope extends Model
{
    use Base_Model;
    use SoftDeletes;
    protected $fillable = ['name', 'added_by'];
    protected $table = 'm_aegis_scopes';

    public function projects()
    {
        return $this->hasMany(Project::class, 'scope_id', 'id');
    }
}
