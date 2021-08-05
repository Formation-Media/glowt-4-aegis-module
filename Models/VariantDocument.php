<?php

namespace Modules\AEGIS\Models;

use Illuminate\Database\Eloquent\Model;

class VariantDocument extends Model
{
    use \App\Models\Base_Model;
    protected $fillable = [];
    protected $table = 'm_aegis_variant_document';

    public function document(){
        return $this->hasOne(\Modules\DocumentManagement\Models\Document::class, 'document_id', 'id');
    }

    public function project_variant(){
        return $this->hasOne(ProjectVariant::class, 'project_id', 'id');
    }
}
