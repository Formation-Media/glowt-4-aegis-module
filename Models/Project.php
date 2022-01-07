<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [
        'added_by',
        'description',
        'name',
        'reference',
        'scope_id',
        'type_id',
    ];
    protected $table = 'm_aegis_projects';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function scope()
    {
        return $this->belongsTo(Scope::class, 'scope_id', 'id');
    }
    public function variants()
    {
        return $this->hasMany(ProjectVariant::class, 'project_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'added_by', 'id');
    }
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}
