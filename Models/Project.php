<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [
        'added_by',
        'company_id',
        'description',
        'name',
        'reference',
        'scope_id',
        'status',
        'type_id',
    ];
    protected $table = 'm_aegis_projects';

    public $model_link = '/a/m/AEGIS/projects/project/{{id}}';

    public function getDetailsAttribute()
    {
        return [
            'dictionary.reference' => [
                'icon'  => 'hashtag',
                'value' => $this->reference,
            ],
            'dictionary.company' => [
                'icon'  => 'building',
                'value' => $this->company->name,
            ],
        ];
    }
    public function title(): Attribute
    {
        return new Attribute(
            get: fn () => $this->reference.': '.$this->name,
        );
    }
    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'scope_id', 'id');
    }
    public function phases()
    {
        return $this->variants();
    }
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id')->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'added_by', 'id');
    }
    public function variants()
    {
        return $this->hasMany(ProjectVariant::class, 'project_id', 'id');
    }
}
