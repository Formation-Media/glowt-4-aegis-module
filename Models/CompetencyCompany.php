<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyCompany extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [
        'company_id'
    ];
    protected $table = 'm_aegis_competency_company';
}
