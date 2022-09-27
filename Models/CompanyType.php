<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;

class CompanyType extends Model
{
    use Base_Model;

    protected $fillable = [
        'company_id',
        'type_id',
    ];
    protected $table = 'm_aegis_company_types';
}
