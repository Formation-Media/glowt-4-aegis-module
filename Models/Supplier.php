<?php

namespace Modules\Aegis\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [];
    protected $table = 'm_aegis_suppliers';
}
