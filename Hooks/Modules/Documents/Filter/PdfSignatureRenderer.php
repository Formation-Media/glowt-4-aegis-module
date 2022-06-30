<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use App\Helpers\Dates;
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
            $author_reference = $pdf->document->metas->firstWhere('key', 'author_reference');
            $author_signature = File::where('hex_id', $author->getMeta('documents.signature'))->first();
            $company          = null;
            $document_details = [];
            $document_meta    = $pdf->document->getMeta()->toArray();
            $items            = $pdf->document->document_approval_process_items->where('status', 'Approved');
            $job_title        = null;
            $signature_height = 35;
            $spacer           = [0, 5];
            $variant_document = VariantDocument::firstWhere('document_id', $pdf->document->id);

            if (isset($document_meta['author_company'])) {
                $company = Company::withTrashed()->find($document_meta['author_company'])->name;
            }
            if (isset($document_meta['author_role'])) {
                $job_title = JobTitle::find($document_meta['author_role'])->name;
            }

            if ($company) {
                $document_details['dictionary.company'] = $company;
            }

            $document_details = array_merge(
                $document_details,
                [
                    'dictionary.title'           => $pdf->document->name,
                    'aegis::phrases.document-id' => $variant_document->reference,
                    'dictionary.category'        => $pdf->document->category->name,
                    'dictionary.issue'           => $variant_document->issue,
                    'dictionary.date'            => $pdf->document->nice_date('updated_at'),
                ]
            );

            $pdf->ln(3);

            $pdf->columns($document_details, 1);
            $pdf->hr(1, 5);

            $pdf->h4(___('dictionary.author'));
            $pdf->ln(2);

            $author_data = [
                'aegis::phrases.document-id'        => $variant_document->reference,
                'dictionary.issue'                  => $variant_document->issue,
                'documents::phrases.signature-date' => $author_reference ? Dates::date($author_reference->created_at) : null,
                'dictionary.stage'                  => ___('dictionary.author'),
                'documents::phrases.signatory-name' => $pdf->document->created_by->name,
                'aegis::phrases.job-title'          => $job_title,
            ];
            if ($company) {
                $author_data['dictionary.company'] = $company;
            }
            $top = $pdf->getY();

            $pdf->columns($author_data, 1);

            $bottom = $pdf->getY();

            if (isset($author_signature)) {
                list($width, $height) = getimagesize($author_signature->absolute_path);
                $ratio = $height / $width;
                $pdf->Image(
                    $author_signature->absolute_path,
                    $pdf->getUsableWidth() / 2,
                    $top,
                    $signature_height,
                    $signature_height * $ratio
                );
                $top += $pdf->px2mm($signature_height);
            }

            $pdf->setXY($pdf->getUsableWidth() / 2, $top);
            if (isset($document_meta['author_reference'])) {
                $pdf->p($document_meta['author_reference']);
            }

            $pdf->setY($bottom);

            $pdf->hr(...$spacer);

            if ($items) {
                foreach ($items as $item) {
                    $pdf->CheckPageBreak(50);
                    $item_details = DocumentApprovalItemDetails::where('approval_item_id', $item->id)->first();
                    $job_title    = $item_details->job_title->name ?? null;
                    $signature    = File::where('hex_id', $item->agent->getMeta('documents.signature'))->first();

                    $pdf->h4($item->approval_process_item->approval_stage->name);
                    $pdf->ln(2);
                    $top = $pdf->getY();

                    $details = [
                        'aegis::phrases.document-id'        => $variant_document->reference,
                        'dictionary.issue'                  => $variant_document->issue,
                        'documents::phrases.signature-date' => $item->nice_date('created_at'),
                        'dictionary.stage'                  => $item->approval_process_item->approval_stage->name,
                        'documents::phrases.signatory-name' => $item->agent->name,
                    ];

                    if ($job_title) {
                        $details['aegis::phrases.job-title'] = $job_title;
                    }
                    if ($company) {
                        $details['dictionary.company'] = $company;
                    }

                    $pdf->columns($details, 1);

                    $bottom = $pdf->getY();

                    if ($signature) {
                        list($width, $height) = getimagesize($signature->absolute_path);
                        $ratio = $height / $width;
                        $pdf->Image(
                            $signature->absolute_path,
                            $pdf->getUsableWidth() / 2,
                            $top,
                            $signature_height,
                            $signature_height * $ratio
                        );
                        $top += $pdf->px2mm($signature_height);
                    }

                    $pdf->setXY($pdf->getUsableWidth() / 2, $top);

                    $pdf->p($item->reference);

                    $pdf->setY($bottom);

                    $pdf->hr(...$spacer);
                }
            }
        };
    }
}
