<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use App\Models\File;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\VariantDocument;

class PdfSignatureRenderer
{
    public static function run(&$pdf)
    {
        $pdf = function ($pdf) {
            $author           = $pdf->document->created_by;
            $author_signature = File::where('hex_id', $author->getMeta('documents.signature'))->first();
            $company          = null;
            $document_meta    = $pdf->document->getMeta()->toArray();
            $items            = $pdf->document->document_approval_process_items->where('status', 'Approved');
            $job_title        = null;
            $signature_height = 50;
            $top_margin       = 10;
            $variant_document = VariantDocument::firstWhere('document_id', $pdf->document->id);

            if (isset($document_meta['author_company'])) {
                $company = Company::withTrashed()->find($document_meta['author_company'])->name;
            }
            if (isset($document_meta['author_role'])) {
                $job_title = JobTitle::find($document_meta['author_role'])->name;
            }

            $details = [
                'documents::phrases.signature-reference' => $document_meta['author_reference'] ?? null,
                'dictionary.stage'                       => ___('dictionary.author'),
                'documents::phrases.signatory-name'      => $pdf->document->created_by->name,
                'aegis::phrases.job-title'               => $job_title,
                'dictionary.date'                        => $pdf->document->nice_datetime('updated_at'),
                'aegis::phrases.document-id'             => $variant_document->reference,
                'dictionary.issue'                       => $variant_document->issue,
            ];

            $pdf->ln($top_margin);
            $pdf->resetFillColor();

            if ($company) {
                $pdf->p(___('dictionary.for').' '.$company);
            }

            if (isset($author_signature)) {
                list($width, $height) = getimagesize(storage_path($author_signature->storage_path));
                $ratio = $height / $width;
                $pdf->Image('../storage'.$author_signature->storage_path, null, null, $signature_height, $signature_height * $ratio);
            }
            $pdf->columns($details, 1);

            if ($items) {
                foreach ($items as $item) {
                    $pdf->addPage();
                    $pdf->ln($top_margin);

                    $item_details = DocumentApprovalItemDetails::where('approval_item_id', $item->id)->first();
                    $signature    = File::where('hex_id', $item->agent->getMeta('documents.signature'))->first();

                    $company   = $item_details->company->name ?? null;
                    $job_title = $item_details->job_title->name ?? null;

                    $details = [
                        'documents::phrases.signature-reference' => $item->reference,
                        'dictionary.stage'                       => $item->approval_process_item->approval_stage->name,
                        'documents::phrases.signatory-name'      => $item->agent->name,
                    ];

                    if ($signature) {
                        list($width, $height) = getimagesize(storage_path($signature->storage_path));
                        $ratio = $height / $width;
                        $pdf->Image('../storage'.$signature->storage_path, null, null, $signature_height, $signature_height * $ratio);
                    }

                    if ($job_title) {
                        $details['aegis::phrases.job-title'] = $job_title;
                    }
                    if ($company) {
                        $details['dictionary.company'] = $company;
                    }

                    $details['dictionary.date']            = $item->nice_datetime('updated_at');
                    $details['aegis::phrases.document-id'] = $variant_document->reference;
                    $details['dictionary.issue']           = $variant_document->issue;

                    $pdf->columns($details, 1);
                }
            }
        };
    }
}
