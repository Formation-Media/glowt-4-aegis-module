<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Type;

class ProjectsImport implements ToCollection
{
    private $method;
    private $projects;
    private $stream;
    private $variant_references = [];

    public function __construct(SSEStream $stream, $users, $method)
    {
        $this->method = $method;
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        if ($this->method === 2) {
            return $this->method_2($rows);
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $companies          = Company::withTrashed()->pluck('id', 'abbreviation');
        $projects           = Project::pluck('id', 'reference');
        $customers          = Customer::pluck('id', 'name');
        $types              = Type::pluck('id', 'name');
        $variant_references = ProjectVariant::pluck('reference')->toArray();
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $project_description = $this->row($row, 'PROJECT DESCRIPTION');
            $project_name        = $this->row($row, 'PROJECT NAME');
            $project_reference   = $this->row($row, 'PROJECT IDENTIFICATION');
            $project_type        = $this->row($row, 'PROJECT TYPE');
            $customer_name       = $this->row($row, 'CUSTOMER/SCOPE');
            $variant_description = $this->row($row, 'VARIANT DESCRIPTION');
            $variant_name        = $this->row($row, 'VARIANT NAME');
            $variant_number      = $this->row($row, 'VARIANT NUMBER');
            $project_company     = explode('/', $project_reference)[0];
            if (array_key_exists($customer_name, $customers)) {
                $customer_id = $customers[$customer_name];
            } else {
                $j         = 1;
                $reference = substr(str_replace(' ', '', $customer_name), 0, 3);
                while (Customer::where(['reference' => $reference.$j])->first()) {
                    $j++;
                }
                $customer = Customer::firstOrCreate(
                    [
                        'name' => $customer_name,
                    ],
                    [
                        'reference' => strtoupper($reference.$j),
                        'added_by'  => \Auth::id(),
                    ]
                );
                $customer_id               = $customer->id;
                $customers[$customer_name] = $customer_id;
            }
            if (array_key_exists($project_type, $types)) {
                $type_id = $types[$project_type];
            } else {
                $name = $project_type ?? 'Other';
                $type = Type::firstOrCreate(
                    [
                        'name' => $name,
                    ],
                    [
                        'added_by' => \Auth::id(),
                    ]
                );
                $type_id      = $type->id;
                $types[$name] = $type_id;
            }
            if (array_key_exists($customer_name, $customers)) {
                $customer_id = $customers[$customer_name];
            } else {
                $project = Project::firstOrCreate(
                    [
                        'reference' => $project_reference,
                    ],
                    [
                        'added_by'    => \Auth::id(),
                        'company_id'  => $companies[$project_company],
                        'description' => $project_description ?? '',
                        'name'        => strlen($project_name) > 191 ? substr($project_name, 0, 188).'...' : $project_name,
                        'customer_id' => $customer_id,
                        'type_id'     => $type_id,
                    ]
                );
                $project_id                   = $project->id;
                $projects[$project_reference] = $project_id;
            }
            $j         = 1;
            $reference = substr(str_replace(' ', '', $variant_number), 0, 3).'-';
            while (in_array($reference.$j, $variant_references) || ProjectVariant::where(['reference' => $reference.$j])->first()) {
                $j++;
            }
            $variant_references[] = $reference.$j;
            ProjectVariant::firstOrCreate(
                [
                    'project_id'     => $project->id,
                    'is_default'     => $variant_number === 0 ? true : false,
                    'variant_number' => $variant_number,
                ],
                [
                    'added_by'    => \Auth::id(),
                    'description' => $variant_description,
                    'name'        => $variant_name ?? ($variant_number === 0 ? 'Default' : 'Variant '.$variant_number),
                    'reference'   => strtoupper($reference.$j),
                ]
            );
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
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
    private function method_2($rows)
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
}
