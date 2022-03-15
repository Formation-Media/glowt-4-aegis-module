<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;

class CompetencyDetail extends Model
{
    use Base_Model;

    protected $fillable = [
        'competency_id',
        'company_id',
        'live_document',
    ];
    protected $table = 'm_aegis_competency_details';

    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }
}
