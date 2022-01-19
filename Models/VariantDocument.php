<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Models\Document;

class VariantDocument extends Model
{
    use Base_Model;
    protected $fillable = [
        'created_at',
        'document_id',
        'reference',
        'variant_id',
    ];
    protected $table = 'm_aegis_variant_documents';

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
    public function project_variant()
    {
        return $this->belongsTo(ProjectVariant::class, 'variant_id', 'id');
    }
}
