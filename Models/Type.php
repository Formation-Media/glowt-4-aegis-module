<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [
        'added_by',
        'company_id',
        'name',
        'parent_id',
    ];
    protected $table = 'm_aegis_types';

    public function parents(): Attribute
    {
        return new Attribute(
            get: function () {
                $parent  = $this->parent;
                $parents = [];
                while ($parent) {
                    $parents[$parent->id] = $parent->name;
                    $parent               = $parent->parent;
                }
                return $parents;
            }
        );
    }
    public function children()
    {
        return $this->hasMany(Type::class, 'parent_id');
    }
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'm_aegis_company_types');
    }
    public function parent()
    {
        return $this->belongsTo(Type::class, 'parent_id');
    }
    public function projects()
    {
        return $this->hasMany(Project::class, 'type_id', 'id');
    }
}
