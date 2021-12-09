<?php

namespace Modules\AEGIS\Models;

use App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;

class DocumentApprovalItemCompany extends Model
{
    use Base_Model;

    protected $fillable = [
        'company_id',
        'document_approval_item_id',
    ];
    protected $table = 'm_aegis_document_approval_item_company';
}
