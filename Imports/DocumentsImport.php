<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DocumentsImport implements ToCollection
{
    private $errors;
    private $projects;
    private $stream;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        if (\Storage::exists('modules/aegis/import/projects_and_documents.json')) {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/projects_and_documents.json'),
                true
            );
        } else {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/projects.json'),
                true
            );
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updating data with documents',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $row = $this->row($row);

            if (!isset($this->projects[$row['project-reference']])) {
                $this->errors['Documents'][$row['reference']] = 'Project not found';
                continue;
            }

            if (!isset($this->projects[$row['project-reference']]['phases'][$row['phase-number']])) {
                $this->errors['Documents'][$row['reference']] = 'Project Phase ('.$row['phase-number'].') not found';
                continue;
            }

            $this->projects[$row['project-reference']]['phases'][$row['phase-number']]['documents'][$row['reference']] = [
                'category'        => $row['type'],
                'category_prefix' => $row['prefix'],
                'created_at'      => $row['created_at'],
                'created_by'      => $row['created_by'],
                'feedback_list'   => $row['fbl'],
                'issue'           => $row['issue'],
                'name'            => $row['name'],
                'statuses'        => [],
                'approval'        => [],
                'comments'        => [],
            ];
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/projects_and_documents.json', json_encode($this->projects));
    }
    private function date_convert($date, $time = null)
    {
        if (!is_numeric($date)) {
            if (strpos($date, '/') !== false) {
                list($day, $month, $year) = explode('/', $date);
            } elseif (strpos($date, '-') !== false) {
                list($day, $month, $year) = explode('-', $date);
                $month_abbreviations = [
                    'Jan' => '01',
                    'Feb' => '02',
                    'Mar' => '03',
                    'Apr' => '04',
                    'May' => '05',
                    'Jun' => '06',
                    'Jul' => '07',
                    'Aug' => '08',
                    'Sep' => '09',
                    'Oct' => '10',
                    'Nov' => '11',
                    'Dec' => '12',
                ];
                $months = [
                    'January'   => '01',
                    'February'  => '02',
                    'March'     => '03',
                    'April'     => '04',
                    'May'       => '05',
                    'June'      => '06',
                    'July'      => '07',
                    'August'    => '08',
                    'September' => '09',
                    'October'   => '10',
                    'November'  => '11',
                    'December'  => '12',
                ];
                if (strlen($month) === 3) {
                    $month = $month_abbreviations[$month];
                } else {
                    $month = $months[$month];
                }
                if (strlen($year) === 2) {
                    $year = 20 . $year;
                }
            }
            $date_as_time = strtotime($year.'-'.$month.'-'.$day);
        } else {
            $date_as_time = strtotime('1900-01-01 + '.($date - 2).' days');
        }
        $created_date = date('Y-m-d', $date_as_time);
        $created_time = date('H:i:s', $time * 24 * 60 * 60);
        return $created_date.' '.$created_time;
    }
    private function row($row)
    {
        $data = [
            'reference'         => $row[1],
            'phase-number'      => $row[2],
            'phase-name'        => $row[3],
            'name'              => $row[4],
            'type'              => $row[5] ?? 'Other',
            'created_at'        => $row[6] ? $this->date_convert($row[6], $row[14]) : date('Y-m-d H:i:s'),
            'created_by'        => strtolower($row[7]),
            'description'       => $row[8],
            'project-reference' => $row[9],
            'project-name'      => $row[10],
            'issue'             => $row[13],
            'prefix'            => $row[18],
            'fbl'               => null,
        ];

        if (strlen($data['name']) > 191) {
            $data['name']        = substr($data['name'], 0, 188).'...';
            $data['description'] = '...'.substr($data['name'], 188)."\r\n\r\n".$data['description'];
        }
        if (strlen($data['project-name']) > 191) {
            $data['project-name'] = substr($data['project-name'], 0, 188).'...';
        }

        if ($data['prefix'] === 'FBL') {
            $data['fbl'] = [
                'final' => false,
                'type'  => $row[16],
                'name'  => $row[17],
            ];
        }

        return $data;
    }
}
