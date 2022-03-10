<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use App\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [];
    protected $table    = 'm_aegis_companies';

    public function pdf_footer()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function scopeMDSS(Builder $query)
    {
        return $query->where('show_for_mdss', true);
    }
}
