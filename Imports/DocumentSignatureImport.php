<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Company;

class DocumentSignatureImport implements ToCollection
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
        $to_retry = [];
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading data',
        ]);
        $this->companies = Company::withTrashed()->pluck('abbreviation')->toArray();
        $this->errors    = json_decode(
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

            extract($this->row($row));

            if (!isset($this->projects[$project_reference])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project '.$project_reference.' not Found (L'.__LINE__.')';
                \Debug::debug('Project '.$project_reference.' not Found');
                // \Debug::debug([
                //     $project_reference => [
                //         'added_by'    => $user_id,
                //         'company'     => explode('/', $project_reference)[0],
                //         'customer'    => 'Other',
                //         'description' => '',
                //         'name'        => $project_name,
                //         'type'        => 'Other',
                //         'phases'      => [],
                //     ],
                // ]);
                $this->stream->stop();
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project '.$project_reference.', Phase '
                    .$phase_number.' not found (L'.__LINE__.')';
                \Debug::debug('Project '.$project_reference.', Phase '.$phase_number.' not found');
                $this->stream->send([
                    'message' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Creating Project \''
                        .$project_reference.'\' from document signature data',
                ]);
                $this->stream->stop();
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number]['documents'])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project '.$project_reference.', Phase '
                    .$phase_number.' has no documents (L'.__LINE__.')';
                \Debug::debug('Project '.$project_reference.', Phase '.$phase_number.' has no documents');
                $this->stream->stop();
                continue;
            }
            if (!isset($this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference])) {
                // Try in other project references
                $exploded_document_reference = explode('/', $document_reference);
                $old_document_id             = $exploded_document_reference[1];
                $project_id                  = implode('/', array_slice($exploded_document_reference, 0, 2));
                $variant                     = false;
                $old_abbreviation            = explode('/', $project_reference);
                foreach ($this->companies as $abbreviation) {
                    $abbreviation = strtoupper($abbreviation);
                    if ($old_abbreviation[0] === $abbreviation) {
                        continue;
                    }
                    $old_abbreviation[0] = $abbreviation;
                    $new_project_id      = implode('/', $old_abbreviation);
                    if (array_key_exists($new_project_id, $this->projects)) {
                        foreach ($this->projects[$new_project_id]['phases'] as $variant_number => $variant_details) {
                            if (array_key_exists('documents', $variant_details)
                                && array_key_exists($document_reference, $variant_details['documents'])
                            ) {
                                $project_reference = $new_project_id;
                                $variant           = $variant_number;
                                break 2;
                            }
                        }
                    }
                }
                if ($variant === false) {
                    $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference] = [
                        'category'        => $document_type,
                        'category_prefix' => $document_prefix,
                        'created_at'      => $created_at,
                        'created_by'      => $document_created_by,
                        'created_by_role' => null,
                        'feedback_list'   => null,
                        'issue'           => $document_issue,
                        'name'            => $document_name,
                        'statuses'        => [],
                        'approval' => [
                            'author' => [],
                        ],
                        'author' => [
                            'reference' => 'N/a',
                        ],
                        'comments' => [],
                    ];
                }
            }

            $document = $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference];

            if ($reviewer['author']) {
                $document['approval']['reviewer'][$document_issue][0][$reviewer['author']] = [
                    'comments'            => $reviewer['comments'],
                    'created_at'          => $created_at,
                    'role'                => $reviewer['role'],
                    'signature_reference' => '',
                    'status'              => $status,
                    'updated_at'          => $reviewer['date'],
                ];
            }

            if ($approver['author']) {
                $document['approval']['approver'][$document_issue][0][$approver['author']] = [
                    'comments'            => $approver['comments'],
                    'created_at'          => $approver['date'],
                    'role'                => $approver['role'],
                    'signature_reference' => '',
                    'status'              => $status,
                    'updated_at'          => $approver['date'],
                ];
            }
            if ($assessor_1) {
                $document['approval']['assessor'][$document_issue][0][$assessor_1] = [
                    'comments'            => '',
                    'created_at'          => $created_at,
                    'role'                => 'Assessor',
                    'signature_reference' => 'N/a',
                    'status'              => $status,
                    'updated_at'          => $created_at,
                ];
            }
            if ($assessor_2) {
                $document['approval']['assessor'][$document_issue][1][$assessor_2] = [
                    'comments'            => '',
                    'created_at'          => $created_at,
                    'role'                => 'Assessor',
                    'signature_reference' => 'N/a',
                    'status'              => $status,
                    'updated_at'          => $created_at,
                ];
            }
            if ($submitted['comments']) {
                $comments[] = [
                    'author'     => $submitted['author'],
                    'created_at' => $created_at,
                    'content'    => $submitted['comments'],
                    'updated_at' => $submitted['date'],
                ];
            }

            $document['comments']        = $comments;
            $document['issue']           = max($document['issue'], $document_issue);
            $document['statuses'][]      = $status;
            $document['submitted_at']    = $submitted['date'];
            $document['submitted_by']    = $submitted['author'];
            $document['created_by_role'] = $role;

            if ($document['category_prefix'] === 'FBL') {
                $document['feedback_list']['final'] = $fbl_final;
            }

            $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference] = $document;

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 2),
            ]);
        }
        \Storage::put('modules/aegis/import/retry_document_signatures.json', json_encode($to_retry, JSON_PRETTY_PRINT));
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors, JSON_PRETTY_PRINT));
        \Storage::put('modules/aegis/import/projects_and_document_signatures.json', json_encode($this->projects, JSON_PRETTY_PRINT));
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
            } else {
                \Debug::debug($date, $time, debug_backtrace(0, 2));
                $this->stream->stop();
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
        $keys = [
            0  => 'ID',
            1  => 'DOC-IDENTIFICATION',
            2  => 'ISSUE',
            3  => 'DOC-NAME',
            4  => 'DOC-TYPE',
            5  => 'CREATION-DATE',
            6  => 'AUTHOR',
            7  => 'DOC-DESCRIPTION',
            8  => 'PROJECT IDENTIFICATION',
            9  => 'VARIANT NUMBER',
            10 => 'PROJECT NAME',
            11 => 'STATUS',
            12 => 'SUBMIT-DATE',
            13 => 'REVIEWER',
            14 => 'REVIEW-DATE',
            15 => 'APPROVER',
            16 => 'APPROVAL-DATE',
            17 => 'REJECT DATE',
            18 => 'AUTHOR-ROLE',
            19 => 'REVIEWER-ROLE',
            20 => 'APPROVER-ROLE',
            21 => 'COMMENT-REVIEWER',
            22 => 'SUBMITTER-NAME',
            23 => 'COMMENT-APPROVER',
            24 => 'REJECTED-BY',
            25 => 'CRE-TIME',
            26 => 'SUB-TIME',
            27 => 'REV-TIME',
            28 => 'REJ-TIME',
            29 => 'APP-TIME',
            30 => 'DOC-LETTER',
            31 => 'FBL_FINAL',
            32 => 'ASSESSOR_1',
            33 => 'ASSESSOR_2',
            34 => 'ADD_FBL_ASSESSOR',
            35 => 'CLOSED',
            36 => 'COMMENT-SUBMITTER',
            37 => 'PROPOSAL-VALUE',
        ];
        $statuses = [
            'APPROVED'  => 'Approved',
            'REJECTED'  => 'Rejected',
            'REVIEWED'  => 'Awaiting Decision',
            'SUBMITTED' => 'Awaiting Decision',
        ];
        $row = $row->toArray();
        foreach ($row as &$cell) {
            $cell = trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $cell));
        }
        $row  = array_combine($keys, array_slice($row, 0, count($keys)));

        $data = [
            'approver'    => [
                'author'   => strtolower(str_replace('---', '', $row['APPROVER'])),
                'date'     => $row['APPROVAL-DATE'] && $row['APPROVAL-DATE'] !== '---'
                    ? $this->date_convert($row['APPROVAL-DATE'], $row['APP-TIME'])
                    : null,
                'comments' => str_replace('---', '', $row['COMMENT-APPROVER']),
                'role'     => str_replace('---', '', $row['APPROVER-ROLE']),
            ],
            'assessor_1'         => $row['ASSESSOR_1'],
            'assessor_2'         => $row['ASSESSOR_2'],
            'created_at'         => $row['CREATION-DATE']
                ? $this->date_convert($row['CREATION-DATE'], $row['CRE-TIME'])
                : null,
            'document_created_by' => strtolower($row['AUTHOR']),
            'document_issue'      => $row['ISSUE'],
            'document_name'       => $row['DOC-NAME'],
            'document_prefix'     => $row['DOC-LETTER'],
            'document_reference'  => $row['DOC-IDENTIFICATION'],
            'document_type'       => $row['DOC-TYPE'] ?? 'Other',
            'fbl_final'           => strtolower($row['FBL_FINAL']) === 'yes' ? true : false,
            'phase_number'        => $row['VARIANT NUMBER'],
            'project_name'        => $row['PROJECT NAME'],
            'project_reference'   => $row['PROJECT IDENTIFICATION'],
            'reviewer'            => [
                'author'   => strtolower(str_replace('---', '', $row['REVIEWER'])),
                'date'     => $row['REVIEW-DATE'] && $row['REVIEW-DATE'] !== '---'
                    ? $this->date_convert($row['REVIEW-DATE'], $row['REV-TIME'])
                    : null,
                'comments' => str_replace('---', '', $row['COMMENT-REVIEWER']),
                'role'     => str_replace('---', '', $row['REVIEWER-ROLE']),
            ],
            'role'      => str_replace('---', '', $row['AUTHOR-ROLE']),
            'status'    => $statuses[$row['STATUS']],
            'submitted' => [
                'author'   => strtolower(str_replace('---', '', $row['SUBMITTER-NAME'])),
                'comments' => str_replace('---', '', $row['COMMENT-SUBMITTER']),
                'date'     => $row['SUBMIT-DATE'] ? $this->date_convert($row['SUBMIT-DATE'], $row['SUB-TIME']) : null,
            ],
        ];

        return $data;
    }
}
