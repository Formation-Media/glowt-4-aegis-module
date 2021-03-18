<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [
        'name',
        'status'
    ];
    protected $table = 'm_aegis_job_titles';
    public function scopeFormatted($query){
        $return=array();
        if($grades=$query->get()){
            $return=array_column($grades->toArray(),'name','id');
        }
        return $return;
    }
}
