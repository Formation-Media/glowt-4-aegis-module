<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use Modules\AEGIS\Helpers\Icons;
use Modules\AEGIS\Models\VariantDocument;

class CardViewResult
{
    public static function run(&$data, $module)
    {
        if (isset($data['request']->module)
            && $data['request']->module === 'Documents'
            && $data['request']->model === 'Document'
        ) {
            $additional_details = [];
            $variant_document   = VariantDocument
                ::with([
                    'project_variant',
                    'project_variant.project',
                    'project_variant.project.company',
                ])
                ->firstWhere('document_id', $data['result']->id);
            if ($variant_document) {
                $additional_details['dictionary.customer'] = [
                    'icon'  => Icons::customer(),
                    'value' => $variant_document->project_variant->project->customer->name ?? null,
                ];
                $additional_details['dictionary.project'] = [
                    'icon'  => Icons::project(),
                    'value' => $variant_document->project_variant->project->reference ?? null,
                ];
                $additional_details['dictionary.issue'] = [
                    'icon'  => Icons::issue(),
                    'value' => $variant_document->issue ?? null,
                ];
                $additional_details['dictionary.company'] = [
                    'icon'  => Icons::company(),
                    'value' => $variant_document->project_variant->project->company->name,
                ];
            }
            $data['details'] = array_merge(
                $additional_details,
                $data['details']
            );
            if (isset($variant_document->reference)) {
                $data['subtitle'] = $variant_document->reference;
            }
        }
    }
}
