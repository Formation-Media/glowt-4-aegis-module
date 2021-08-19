<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectVariant extends Model
{
    use Base_Model;
    use SoftDeletes;
    protected $fillable = [];
    protected $table = 'm_aegis_project_variants';

    public function variant_documents(){
        return $this->hasMany(VariantDocument::class, 'variant_id', 'id');
    }
    public function documents(){
        return $this->belongsToMany(\Modules\Documents\Models\Document::class, 'm_aegis_document_variants','variant_id', 'document_id');
    }
   public function project(){
        return $this->belongsTo(Project::class, 'project_id', 'id' );
    }
}
