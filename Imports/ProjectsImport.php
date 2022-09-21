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
        $errors         = [];
        $user_id        = \Auth::id();
        $this->projects = json_decode(
            \Storage::get('modules/aegis/import/projects.json'),
            true
        );
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            extract($this->row($row));

            if (array_key_exists($reference, $this->projects)) {
                $this->errors['Projects'][$reference] = 'Duplicate Project ('.$reference.') shipping additional projects.';
                continue;
            }

            $this->projects[$reference]['added_by']    = $user_id;
            $this->projects[$reference]['company']     = $company;
            $this->projects[$reference]['description'] = $description;
            $this->projects[$reference]['name']        = $name;
            $this->projects[$reference]['customer']    = $customer;
            $this->projects[$reference]['type']        = $type;

            $this->projects[$reference]['phases'][$phase_number]['name']        = $phase_name;
            $this->projects[$reference]['phases'][$phase_number]['description'] = $phase_description;
            $this->projects[$reference]['phases'][$phase_number]['reference']   = $phase_number;
            $this->projects[$reference]['phases'][$phase_number]['documents']   = [];

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        ksort($this->projects);
        \Storage::put('modules/aegis/import/projects.json', json_encode($this->projects));
    }
    private function row($row)
    {
        $keys = [
            'PROJECT Internal ID',
            'PROJECT IDENTIFICATION',
            'PROJECT NAME',
            'CUSTOMER/SCOPE',
            'PROJECT DESCRIPTION',
            'PROJECT TYPE',
            'PROVA',
            'PROGRESSIVE NUMBER',
            'VARIANT NAME',
            'VARIANT NUMBER',
            'VARIANT DESCRIPTION',
        ];
        $row  = array_combine($keys, array_slice($row->toArray(), 0, count($keys)));

        $data = [
            'company'           => null,
            'reference'         => $row['PROJECT IDENTIFICATION'],
            'name'              => $row['PROJECT NAME'],
            'customer'          => $row['CUSTOMER/SCOPE'],
            'description'       => $row['PROJECT DESCRIPTION'] ?? '',
            'type'              => $row['PROJECT TYPE'] ?? 'Other',
            'phase_name'        => $row['VARIANT NAME'],
            'phase_number'      => $row['VARIANT NUMBER'],
            'phase_description' => $row['VARIANT DESCRIPTION'],
        ];
        $data['company'] = explode('/', $data['reference'])[0];

        if (strlen($data['name']) > 191) {
            $data['name']        = substr($data['name'], 0, 188).'...';
            $data['description'] = '...'.substr($data['name'], 188)."\r\n\r\n".$data['description'];
        }

        if ($data['phase_number'] === 0) {
            $data['phase-reference'] = $data['reference'];
        } else {
            $data['phase-reference'] = $data['reference'].'/'.str_pad($data['phase_number'], 3, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
