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
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading data',
        ]);
        $user_id = \Auth::id();
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
        $j = 0;
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            extract($this->row($row));

            if (!isset($this->projects[$project_reference])) {
                if (!$project_reference) {
                    $debug = $this->row($row);
                    unset($debug['created_at']);
                    $debug = array_filter($debug);
                    if ($debug) {
                        $this->errors['Projects']['N/a #'. (++$j)] = 'Could not process row: '.json_encode($debug).' (L'.__LINE__.')';
                        $this->stream->send([
                            'message' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Could not process row: '
                                .json_encode($debug),
                        ]);
                    }
                    continue;
                } else {
                    $this->projects[$project_reference] = [
                        'added_by'    => $user_id,
                        'company'     => explode('/', $project_reference)[0],
                        'customer'    => 'Other',
                        'description' => '',
                        'name'        => $project_name,
                        'type'        => 'Other',
                        'phases'      => [],
                    ];
                    $this->stream->send([
                        'message' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Creating Project \''
                            .$project_reference.'\' from document data',
                    ]);
                }
            }

            if (!isset($this->projects[$project_reference]['phases'][$phase_number])) {
                $this->projects[$project_reference]['phases'][$phase_number] = [
                    'name'        => $phase_name,
                    'description' => '',
                    'reference'   => $phase_number,
                    'documents'   => [],
                ];
                $this->stream->send([
                    'message' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Creating Project \''
                        .$project_reference.'\', Phase \''.$phase_number.'\' from document data',
                ]);
            }

            $this->projects[$project_reference]['phases'][$phase_number]['documents'][$reference] = [
                'category'        => $type,
                'category_prefix' => $prefix,
                'created_at'      => $created_at,
                'created_by'      => $created_by,
                'created_by_role' => null,
                'feedback_list'   => $fbl,
                'issue'           => $issue,
                'name'            => $name,
                'statuses'        => [],
                'approval' => [
                    'author' => [],
                ],
                'author' => [
                    'reference' => 'N/a',
                ],
                'comments' => [],
            ];
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 2),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors, JSON_PRETTY_PRINT));
        \Storage::put('modules/aegis/import/projects_and_documents.json', json_encode($this->projects, JSON_PRETTY_PRINT));
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
        if ($time) {
            $created_time = date('H:i:s', $time * 24 * 60 * 60);
        } else {
            $created_time = '00:00:00';
        }
        return $created_date.' '.$created_time;
    }
    private function row($row)
    {
        $keys = [
            'DOC-ID Internal',
            'DOC-IDENTIFICATION',
            'VARIANT NUMBER',
            'VARIANT NAME',
            'DOC-NAME',
            'DOC-TYPE',
            'CREATION-DATE',
            'AUTHOR',
            'DOC-DESCRIPTION',
            'PROJECT-IDENTIFICATION',
            'PROJECT NAME',
            'DOC-PROGR-NUM',
            'PROVA',
            'ISSUE',
            'CRE-TIME',
            'DOC-LETTER',
            'FBL TYPE',
            'PRE-TITLE FBL',
            'GEN_LETTER',
        ];
        $row = $row->toArray();
        foreach ($row as &$cell) {
            $cell = trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $cell));
        }
        $row  = array_combine($keys, array_slice($row, 0, count($keys)));

        $data = [
            'reference'         => $row['DOC-IDENTIFICATION'],
            'phase_number'      => $row['VARIANT NUMBER'],
            'phase_name'        => $row['VARIANT NAME'],
            'name'              => $row['DOC-NAME'],
            'type'              => $row['DOC-TYPE'] ?? 'Other',
            'created_by'        => strtolower($row['AUTHOR']),
            'description'       => $row['DOC-DESCRIPTION'],
            'project_reference' => $row['PROJECT-IDENTIFICATION'],
            'project_name'      => $row['PROJECT NAME'],
            'issue'             => $row['ISSUE'],
            'prefix'            => $row['DOC-LETTER'],
            'fbl'               => null,

            'created_at' => $row['CREATION-DATE']
                ? $this->date_convert($row['CREATION-DATE'], $row['CRE-TIME'])
                : date('Y-m-d H:i:s'),
        ];

        if (strlen($data['name']) > 191) {
            $data['name']        = substr($data['name'], 0, 188).'...';
            $data['description'] = '...'.substr($data['name'], 188)."\r\n\r\n".$data['description'];
        }
        if (strlen($data['project_name']) > 191) {
            $data['project_name'] = substr($data['project_name'], 0, 188).'...';
        }

        if ($data['prefix'] === 'FBL') {
            $data['fbl'] = [
                'final' => false,
                'type'  => $row['FBL TYPE'],
                'name'  => $row['PRE-TITLE FBL'],
            ];
        }

        return $data;
    }
}
