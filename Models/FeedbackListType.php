<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Models\Document;

class FeedbackListType extends Model
{
    use Base_Model;

    protected $fillable = [
        'name',
        'reference',
    ];
    protected $table = 'm_aegis_feedback_list_types';

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
    public function project_variant()
    {
        return $this->belongsTo(ProjectVariant::class, 'variant_id', 'id');
    }
}
