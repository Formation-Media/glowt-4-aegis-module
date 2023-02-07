<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use App\Helpers\Conversions;
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
            $author              = $pdf->document->created_by;
            $author_signature    = File::where('hex_id', $author->getMeta('documents.signature'))->first();
            $company             = null;
            $center_x            = $pdf->getUsableWidth() * .6;
            $document_details    = [];
            $document_meta       = $pdf->document->getMeta()->toArray();
            $items               = $pdf->document->document_approval_process_items->where('status', 'Approved');
            $job_title           = null;
            $max_signature_width = $pdf->getUsableWidth() - $center_x;
            $signature_height    = 35;
            $signature_width     = 0;
            $spacer              = [0, 5];
            $variant_document    = VariantDocument::firstWhere('document_id', $pdf->document->id);

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
                    'dictionary.date'            => $pdf->document->nice_date('submitted_at'),
                ]
            );

            $pdf->ln(3);

            $pdf->columns($document_details, 1);
            $pdf->hr(1, 5);

            $top = $pdf->getY();

            $pdf->h4(___('dictionary.author'));
            $pdf->ln(2);

            $author_data = [
                'aegis::phrases.document-id'        => $variant_document->reference,
                'dictionary.issue'                  => $variant_document->issue,
                'documents::phrases.signature-date' => $pdf->document->nice_date('submitted_at'),
                'documents::phrases.signatory-name' => $pdf->document->created_by->name,
                'aegis::phrases.job-title'          => $job_title,
            ];
            if ($company) {
                $author_data['dictionary.company'] = $company;
            }

            $pdf->columns($author_data, 1);

            $bottom = $pdf->getY();

            if (isset($author_signature) && $author_signature->is_file) {
                list($width, $height) = getimagesize($author_signature->absolute_path);
                if ($height) {
                    $ratio           = $width / $height;
                    $signature_width = $signature_height * $ratio;
                    if ($signature_width > $max_signature_width) {
                        $signature_height = $max_signature_width / $ratio;
                        $signature_width  = $max_signature_width;
                    }

                    $tmp = '/tmp/aegis-mdss-author-signature-'.$variant_document->id.'.png';

                    \Image::make($author_signature->absolute_path)->greyscale()->save($tmp);

                    $pdf->Image(
                        $tmp,
                        $center_x,
                        $top,
                        $signature_width,
                        $signature_height
                    );
                    $y = $top + ($signature_height / 2);    // Top + (signature height/2)
                } else {
                    $y = $top;
                }
            } else {
                $y = $top;
            }

            if (isset($variant_document->document->author_reference)) {
                $pdf->setFont('', 'B', 16);
                $reference_width = $pdf->GetStringWidth($variant_document->document->author_reference);

                $pdf->setXY(max($center_x, $center_x + $signature_width - $reference_width), $y);
                $pdf->setTextColor(7, 76, 141);
                $pdf->Cell(0, 5, $variant_document->document->author_reference);
                $pdf->resetTextColor();
                $pdf->setFont('', '', 16);
            }

            $pdf->setXY($pdf->getLeftMargin(), max($bottom, $top + $signature_height));

            $pdf->hr(...$spacer);

            if ($items) {
                foreach ($items as $item) {
                    if ($item->approval_process_item) {
                        $pdf->CheckPageBreak(50);
                        $item_details    = DocumentApprovalItemDetails::where('approval_item_id', $item->id)->first();
                        $job_title       = $item_details->job_title->name ?? null;
                        $signature       = File::where('hex_id', $item->agent->getMeta('documents.signature'))->first();

                        $signature_width = 0;

                        $top = $pdf->getY();

                        $pdf->h4($item->approval_process_item->approval_stage->name);
                        $pdf->ln(2);

                        $details = [
                            'aegis::phrases.document-id'        => $variant_document->reference,
                            'dictionary.issue'                  => $variant_document->issue,
                            'documents::phrases.signature-date' => $item->nice_date('updated_at'),
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


                        if ($signature && $signature->is_file) {
                            list($width, $height) = getimagesize($signature->absolute_path);
                            $ratio            = $width / $height;
                            $signature_height = $signature_height;
                            $signature_width  = $signature_height * $ratio;
                            if ($signature_width > $max_signature_width) {
                                $signature_height = $max_signature_width / $ratio;
                                $signature_width  = $max_signature_width;
                            }

                            $tmp = '/tmp/aegis-mdss-additional-signature-'
                                .Conversions::to_base_64(str_replace('.', '', microtime(true))).'.png';

                            \Image::make($signature->absolute_path)->greyscale()->save($tmp);

                            $pdf->Image(
                                $tmp,
                                $center_x,
                                $top,
                                $signature_width,
                                $signature_height
                            );

                            $y = $top + ($signature_height / 2);    // Top + (signature height/2)
                        } else {
                            $y = $top;
                        }

                        $pdf->setFont('', 'B', 16);
                        $reference_width = $pdf->GetStringWidth($item->reference);
                        $pdf->setTextColor(7, 76, 141);
                        $pdf->setXY(max($center_x, $center_x + $signature_width - $reference_width), $y);
                        $pdf->Cell(0, 5, $item->reference);
                        $pdf->resetTextColor();
                        $pdf->setFont('', '', 16);

                        $pdf->setY(max($bottom, $top + $signature_height));

                        $pdf->hr(...$spacer);
                    }
                }
            }
        };
    }
}
