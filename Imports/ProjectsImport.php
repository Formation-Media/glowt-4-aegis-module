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
            $row = $this->row($row);

            $this->projects[$row['reference']]['added_by']    = $user_id;
            $this->projects[$row['reference']]['company']     = $row['company'];
            $this->projects[$row['reference']]['description'] = $row['description'];
            $this->projects[$row['reference']]['name']        = $row['name'];
            $this->projects[$row['reference']]['customer']    = $row['customer'];
            $this->projects[$row['reference']]['type']        = $row['type'];

            $this->projects[$row['reference']]['phases'][$row['phase-number']]['name']        = $row['phase-name'];
            $this->projects[$row['reference']]['phases'][$row['phase-number']]['description'] = $row['phase-description'];
            $this->projects[$row['reference']]['phases'][$row['phase-number']]['reference']   = $row['phase-reference'];
            $this->projects[$row['reference']]['phases'][$row['phase-number']]['documents']   = [];

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        ksort($this->projects);
        \Storage::put('modules/aegis/import/projects.json', json_encode($this->projects));
    }
    private function row($row)
    {
        $data = [
            'company'           => null,
            'reference'         => $row[1],
            'name'              => $row[2],
            'customer'          => $row[3],
            'description'       => $row[4] ?? '',
            'type'              => $row[5] ?? 'Other',
            'phase-name'        => $row[8],
            'phase-number'      => $row[9],
            'phase-description' => $row[10],
        ];
        $data['company'] = explode('/', $data['reference'])[0];

        if (strlen($data['name']) > 191) {
            $data['name']        = substr($data['name'], 0, 188).'...';
            $data['description'] = '...'.substr($data['name'], 188)."\r\n\r\n".$data['description'];
        }

        if ($data['phase-number'] === 0) {
            $data['phase-reference'] = $data['reference'];
        } else {
            $data['phase-reference'] = $data['reference'].'/'.str_pad($data['phase-number'], 3, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
