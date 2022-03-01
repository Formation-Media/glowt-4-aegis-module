<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [
        'added_by',
        'name',
        'reference',
    ];
    protected $table = 'm_aegis_scopes';

    public function projects()
    {
        return $this->hasMany(Project::class, 'scope_id', 'id');
    }
}
