<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [];
    protected $table = 'm_aegis_user_grades';
    public function scopeFormatted($query)
    {
        $return = array();
        if ($grades = $query->get()) {
            $return = array_column($grades->toArray(), 'name', 'id');
        }
        return $return;
    }
}
