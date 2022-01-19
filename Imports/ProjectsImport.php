<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Scope;
use Modules\AEGIS\Models\Type;

class ProjectsImport implements ToCollection
{
    private $stream;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $companies          = Company::withTrashed()->pluck('id', 'abbreviation');
        $projects           = Project::pluck('id', 'reference');
        $scopes             = Scope::pluck('id', 'name');
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
            $scope_name          = $this->row($row, 'CUSTOMER/SCOPE');
            $variant_description = $this->row($row, 'VARIANT DESCRIPTION');
            $variant_name        = $this->row($row, 'VARIANT NAME');
            $variant_number      = $this->row($row, 'VARIANT NUMBER');
            $project_company     = explode('/', $project_reference)[0];
            if (array_key_exists($scope_name, $scopes)) {
                $scope_id = $scopes[$scope_name];
            } else {
                $j         = 1;
                $reference = substr(str_replace(' ', '', $scope_name), 0, 3);
                while (Scope::where(['reference' => $reference.$j])->first()) {
                    $j++;
                }
                $scope = Scope::firstOrCreate(
                    [
                        'name' => $scope_name,
                    ],
                    [
                        'reference' => strtoupper($reference.$j),
                        'added_by'  => \Auth::id(),
                    ]
                );
                $scope_id            = $scope->id;
                $scopes[$scope_name] = $scope_id;
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
            if (array_key_exists($scope_name, $scopes)) {
                $scope_id = $scopes[$scope_name];
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
                        'scope_id'    => $scope_id,
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
}
