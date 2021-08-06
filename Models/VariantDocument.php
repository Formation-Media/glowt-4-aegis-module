<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class VariantDocument extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [
        'document_id',
        'variant_id'
    ];
    protected $table = 'm_aegis_variant_documents';

    public function document(){
        return $this->belongsTo(\Modules\DocumentManagement\Models\Document::class,'document_id','id');
    }

    public function project_variant(){
        return $this->belongsTo(ProjectVariant::class, 'variant_id' , 'id');
    }
}
