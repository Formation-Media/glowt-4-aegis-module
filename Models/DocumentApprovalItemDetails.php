<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Modules\Documents\Models\DocumentApprovalProcessItem;

class DocumentApprovalItemDetails extends Model
{
    use Base_Model;

    protected $fillable = [
        'approval_item_id',
        'company_id',
        'job_title_id',
    ];
    protected $table = 'm_aegis_document_approval_item_details';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function item()
    {
        return $this->belongsTo(DocumentApprovalProcessItem::class, 'approval_item_id');
    }
    public function job_title()
    {
        return $this->belongsTo(JobTitle::class);
    }
}
