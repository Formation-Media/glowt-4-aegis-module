<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [];
    protected $table = 'm_aegis_companies';
}
