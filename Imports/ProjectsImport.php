<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProjectsImport implements ToCollection
{
    private $projects;
    private $stream;
    private $variant_references = [];

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $project_description = $this->row($row, 'PROJECT DESCRIPTION');
            $project_name        = $this->row($row, 'PROJECT NAME');
            $project_reference   = $this->row($row, 'PROJECT IDENTIFICATION');
            $project_type        = $this->row($row, 'PROJECT TYPE');
            $customer            = $this->row($row, 'CUSTOMER/SCOPE');
            $variant_description = $this->row($row, 'VARIANT DESCRIPTION');
            $variant_number      = $this->row($row, 'VARIANT NUMBER');

            $project_company = explode('/', $project_reference)[0];

            if (strlen($project_name) > 191) {
                $project_name = substr($project_name, 0, 188).'...';
            }

            $this->projects[$project_reference]['added_by']    = \Auth::id();
            $this->projects[$project_reference]['company']     = $project_company;
            $this->projects[$project_reference]['description'] = $project_description ?? '';
            $this->projects[$project_reference]['name']        = $project_name;
            $this->projects[$project_reference]['customer']    = $customer;
            $this->projects[$project_reference]['type']        = $project_type ?? 'Other';

            $this->projects[$project_reference]['variants'][$variant_number]['description'] = $variant_description;
            $this->projects[$project_reference]['variants'][$variant_number]['documents']   = [];

            $j         = 1;
            $reference = substr(str_replace(' ', '', $variant_number), 0, 3).'-';
            while (in_array($reference.$j, $this->variant_references)) {
                $j++;
            }
            $this->variant_references[] = $reference.$j;

            $this->projects[$project_reference]['variants'][$variant_number]['reference'] = $reference.$j;

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/projects.json', json_encode($this->projects));
    }
    private function row($row, $key)
    {
        $keys = [
            'PROJECT IDENTIFICATION',
            'PROJECT NAME',
            'CUSTOMER/SCOPE',
            'PROJECT DESCRIPTION',
            'PROJECT TYPE',
            'VARIANT NAME',
            'VARIANT NUMBER',
            'VARIANT DESCRIPTION',
        ];
        return $row[array_search($key, $keys)];
    }
}
