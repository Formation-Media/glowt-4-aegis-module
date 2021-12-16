<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;

class VariantDocument extends Model
{
    use Base_Model;
    protected $fillable = [
        'document_id',
        'variant_id',
    ];
    protected $table = 'm_aegis_variant_documents';

    public function document()
    {
        return $this->belongsTo(\Modules\Documents\Models\Document::class, 'document_id', 'id');
    }

    public function project_variant()
    {
        return $this->belongsTo(ProjectVariant::class, 'variant_id', 'id');
    }
}
