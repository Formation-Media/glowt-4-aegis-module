<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DocumentSignatureImport implements ToCollection
{
    private $errors;
    private $projects;
    private $row;
    private $statuses;
    private $stream;

    public function __construct(SSEStream $stream,)
    {
        $this->statuses = [
            'APPROVED'  => 'Approved',
            'REJECTED'  => 'Rejected',
            'REVIEWED'  => 'Awaiting Decision',
            'SUBMITTED' => 'Awaiting Decision',
        ];
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $this->errors = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        if (\Storage::exists('modules/aegis/import/projects_and_document_signatures.json')) {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/projects_and_document_signatures.json'),
                true
            );
        } else {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/projects_and_documents.json'),
                true
            );
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updating data with document signatures',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !isset($row[1])) {
                continue;
            }
            $comments = [];
            $row      = $this->row($row);

            $created_at         = $row['created_at'];
            $document_issue     = $row['document-issue'];
            $document_reference = $row['document-reference'];
            $phase_number       = $row['phase-number'];
            $project_reference  = $row['project-reference'];

            if (!isset($this->projects[$project_reference])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Not Found';
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Phase ('.$phase_number.') not found';
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number]['documents'])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Phase ('.$phase_number.') has no documents';
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number]['documents']
                [$document_reference])
            ) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Phase ('.$phase_number
                    .') does not have a document with reference '.$document_reference;
                continue;
            }

            $document = $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference];

            if ($row['reviewer']['author']) {
                $document['approval']['reviewer'][$document_issue][0][$row['reviewer']['author']] = [
                    'comments'            => $row['reviewer']['comments'],
                    'created_at'          => $created_at,
                    'role'                => $row['reviewer']['role'],
                    'signature_reference' => '',
                    'status'              => $row['status'],
                    'updated_at'          => $row['reviewer']['date'],
                ];
            }

            if ($row['approver']['author']) {
                $document['approval']['approver'][$document_issue][0][$row['approver']['author']] = [
                    'comments'            => $row['approver']['comments'],
                    'created_at'          => $created_at,
                    'role'                => $row['approver']['role'],
                    'signature_reference' => '',
                    'status'              => $row['status'],
                    'updated_at'          => $row['approver']['date'],
                ];
            }
            if ($row['assessor-1']) {
                $document['approval']['assessor'][$document_issue][0][$row['assessor-1']] = [
                    'created_at' => $created_at,
                    'status'     => $row['status'],
                    'updated_at' => $created_at,
                ];
            }
            if ($row['assessor-2']) {
                $document['approval']['assessor'][$document_issue][1][$row['assessor-2']] = [
                    'created_at' => $created_at,
                    'status'     => $row['status'],
                    'updated_at' => $created_at,
                ];
            }
            if ($row['submitted']['comments']) {
                $comments[] = [
                    'author'     => $row['submitted']['author'],
                    'created_at' => $created_at,
                    'content'    => $row['submitted']['comments'],
                    'updated_at' => $created_at,
                ];
            }

            $document['comments']     = $comments;
            $document['issue']        = max($document['issue'], $document_issue);
            $document['statuses'][]   = $row['status'];
            $document['submitted_at'] = $row['submitted']['date'];
            $document['submitted_by'] = $row['submitted']['author'];

            if ($document['category_prefix'] === 'FBL') {
                $document['feedback_list']['final'] = $row['fbl-final'];
            }

            $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference] = $document;

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/projects_and_document_signatures.json', json_encode($this->projects));
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
            'approver'    => [
                'author'   => strtolower(str_replace('---', '', $row[15])),
                'date'     => $row[16] && $row[16] !== '---' ? $this->date_convert($row[16], $row[29]) : null,
                'comments' => str_replace('---', '', $row[23]),
                'role'     => str_replace('---', '', $row[20]),
            ],
            'assessor-1'         => $row[32],
            'assessor-2'         => $row[33],
            'created_at'         => $row[5] ? $this->date_convert($row[5], $row[25]) : date('Y-m-d H:i:s'),
            'document-issue'     => $row[2],
            'document-reference' => $row[1],
            'fbl-final'          => strtolower($row[31]) === 'yes' ? true : false,
            'phase-number'       => $row[9],
            'project-reference'  => $row[8],
            'reviewer'           => [
                'author'   => strtolower(str_replace('---', '', $row[13])),
                'date'     => $row[14] && $row[14] !== '---' ? $this->date_convert($row[14], $row[27]) : null,
                'comments' => str_replace('---', '', $row[21]),
                'role'     => str_replace('---', '', $row[19]),
            ],
            'role'       => str_replace('---', '', $row[18]),
            'status'    => $this->statuses[$row[11]],
            'submitted' => [
                'author'   => strtolower(str_replace('---', '', $row[22])),
                'comments' => str_replace('---', '', $row[36]),
                'date'     => $row[12] ? $this->date_convert($row[12], $row[26]) : date('Y-m-d H:i:s'),
            ],
        ];

        return $data;
    }
}
