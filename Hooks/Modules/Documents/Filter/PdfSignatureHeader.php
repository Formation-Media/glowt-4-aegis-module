<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use Modules\AEGIS\Models\VariantDocument;

class PdfSignatureHeader
{
    public static function run(&$pdf)
    {
        $variant_document = VariantDocument::where('document_id', $pdf->document->id)->first();
        $company          = $variant_document->project_variant->project->company;
        if ($company && $company->pdf_footer) {
            $file = $company->pdf_footer->absolute_path;
            if (is_file($file)) {
                $pdf->setSourceFile($file);
                $temp = $pdf->importPage(1);
                $pdf->useTemplate($temp);
            }
        }
    }
}
