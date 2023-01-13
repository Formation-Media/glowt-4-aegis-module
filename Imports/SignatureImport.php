<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Company;

class SignatureImport implements ToCollection
{
    private $companies;
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
        $this->companies = Company::withTrashed()->pluck('abbreviation')->toArray();
        $this->errors    = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        if (\Storage::exists('modules/aegis/import/project_data.json')) {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/project_data.json'),
                true
            );
        } else {
            $this->projects = json_decode(
                \Storage::get('modules/aegis/import/projects_and_document_signatures.json'),
                true
            );
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Updating data with document signatures',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            extract($this->row($row));

            if ($company === null) {
                $this->errors['Signatures'][$document_reference] = 'Company does not exist (tried from "'
                    .$document_reference.'" and "'.$user_reference.'"). (L'.__LINE__.')';
                \Debug::debug('Company does not exist (tried from "'.$document_reference.'" and "'.$user_reference.'").');
                $this->stream->stop();
                continue;
            }

            foreach ($this->projects as $project_reference => $project) {
                foreach ($project['phases'] as $variant_number => $variant) {
                    if (array_key_exists('documents', $variant)
                        && array_key_exists($document_reference, $variant['documents'])
                        && array_key_exists($issue, $variant['documents'][$document_reference])
                        && array_key_exists('approval', $variant['documents'][$document_reference][$issue])
                        && array_key_exists($role, $variant['documents'][$document_reference][$issue]['approval'])
                        && array_key_exists($issue, $variant['documents'][$document_reference][$issue]['approval'][$role])
                    ) {
                        if ($role === 'author') {
                            $this->projects[$project_reference]['phases'][$variant_number]['documents'][$document_reference][$issue]
                                ['approval'][$role][$issue][] = [
                                    $user_reference => [
                                        'comments'            => '',
                                        'company'             => $company,
                                        'increment'           => $progressive_number,
                                        'signature_reference' => $signature_reference,
                                        'signed_date'         => $date,

                                        'created_at' => date(
                                            'Y-m-d H:i:s',
                                            strtotime($date)
                                        ),
                                        'updated_at' => date(
                                            'Y-m-d H:i:s',
                                            strtotime($date)
                                        ),
                                    ],
                                ];
                        } else {
                            $user_key = false;

                            if (array_key_exists($issue, $variant['documents'][$document_reference][$issue]['approval'][$role])) {
                                foreach ($variant['documents'][$document_reference][$issue]['approval'][$role]
                                    [$issue] as $key => $item
                                ) {
                                    if (array_search($user_reference, array_keys($item)) !== false) {
                                        $user_key = $key;
                                        break;
                                    }
                                }
                            } else {
                                foreach ($variant['documents'][$document_reference][$issue]['approval'][$role] as $items) {
                                    foreach ($items as $key => $item) {
                                        if (array_search($user_reference, array_keys($item)) !== false) {
                                            $user_key = $key;
                                            break 2;
                                        }
                                    }
                                }
                            }
                            if ($user_key === false) {
                                continue;
                            }

                            $user = $variant['documents'][$document_reference][$issue]['approval'][$role][$issue][$user_key]
                                [$user_reference];

                            $user = array_merge(
                                $user,
                                [
                                    'company'             => $company,
                                    'increment'           => $progressive_number,
                                    'signature_reference' => $signature_reference,
                                    'signed_date'         => $date,

                                    'created_at' => date(
                                        'Y-m-d H:i:s',
                                        min(strtotime($date), strtotime($user['created_at'] ?? time()))
                                    ),
                                    'updated_at' => date(
                                        'Y-m-d H:i:s',
                                        max(strtotime($date), strtotime($user['updated_at'] ?? time()))
                                    ),
                                ]
                            );
                            $this->projects[$project_reference]['phases'][$variant_number]['documents'][$document_reference][$issue]
                                ['approval'][$role][$issue][$user_key][$user_reference] = $user;
                        }

                        $found = true;
                        // Skip the rest of the projects and documents we've assigned the data where we can
                        break 2;
                    }
                }
            }
            if (!$found) {
                $exploded_document_reference = explode('/', $document_reference);
                $old_document_id             = $exploded_document_reference[1];
                if (strlen($old_document_id) > 3 && $old_document_id < 9900) {
                    $project_id = implode('/', array_slice($exploded_document_reference, 0, 2));
                    $variant    = false;

                    if (!array_key_exists($project_id, $this->projects)) {
                        $this->errors['Signatures'][$document_reference] = 'Project '.$project_id.' does not exist. (L'.__LINE__.')';
                        \Debug::debug('Project '.$project_id.' does not exist.');
                        $this->stream->stop();
                        continue;
                    }
                    if (!array_key_exists('phases', $this->projects[$project_id])) {
                        $this->errors['Signatures'][$document_reference] = 'Project '.$project_id.' does not have any phases. (L'
                            .__LINE__.')';
                        \Debug::debug('Project '.$project_id.' does not have any phases.');
                        $this->stream->stop();
                        continue;
                    }
                    foreach ($this->projects[$project_id]['phases'] as $variant_number => $variant_details) {
                        if (array_key_exists('documents', $variant_details)
                            && array_key_exists($document_reference, $variant_details['documents'])
                        ) {
                            $variant = $variant_number;
                            break;
                        }
                    }
                    // Attempt with other companies
                    if ($variant === false) {
                        $old_abbreviation = explode('/', $project_id);
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
                                        $project_id = $new_project_id;
                                        $variant    = $variant_number;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    if ($variant !== false) {
                        if (!array_key_exists(
                            $issue,
                            $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference]
                        )) {
                            // If the issue doesn't exist...
                            $previous_issue = false;
                            // ...See if previous issues exist...
                            for ($j = $issue - 1; $j !== 0; $j--) {
                                if (array_key_exists(
                                    $j,
                                    $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference]
                                )) {
                                    $previous_issue = $j;
                                    // ...Use the latest version...
                                    break;
                                }
                            }
                            if ($previous_issue !== false) {
                                // ...to assign to the current issue
                                $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference][$issue]
                                    = $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference]
                                        [$previous_issue];
                            } else {
                                // No previous issue
                                \Debug::debug($document_reference, $issue);
                                $this->stream->stop();
                            }
                        }
                        if (array_key_exists(
                            'approval',
                            $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference][$issue]
                        )) {
                            if (array_key_exists(
                                $role,
                                $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference][$issue]
                                    ['approval']
                            )) {
                                $user_key  = false;
                                if (array_key_exists($issue, $this->projects[$project_id]['phases'][$variant]['documents']
                                    [$document_reference][$issue]['approval'][$role])
                                ) {
                                    foreach ($this->projects[$project_id]['phases'][$variant]['documents']
                                        [$document_reference][$issue]['approval'][$role][$issue] as $key => $item
                                    ) {
                                        if (array_search($user_reference, array_keys($item)) !== false) {
                                            $issue_key = $issue;
                                            $user_key  = $key;
                                            break;
                                        }
                                    }
                                } else {
                                    if (array_key_exists($issue, $this->projects[$project_id]['phases'][$variant]['documents']
                                    [$document_reference][$issue]['approval'][$role])) {
                                        foreach ($this->projects[$project_id]['phases'][$variant]['documents'][$document_reference]
                                            [$issue]['approval'][$role][$issue] as $key => $item
                                        ) {
                                            if (array_search($user_reference, array_keys($item)) !== false) {
                                                $issue_key = $issue;
                                                $user_key  = $key;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                                if ($user_key === false) {
                                    $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference][$issue]
                                        ['author']['reference'] = $signature_reference;
                                    $this->projects[$project_id]['phases'][$variant]['documents'][$document_reference][$issue]
                                        ['approval'][$role][$issue][0][$user_reference] = [
                                            'comments'            => '',
                                            'created_at'          => $date,
                                            'role'                => $user_role,
                                            'signature_reference' => $signature_reference,
                                            'status'              => 'Approved',
                                            'updated_at'          => $date,
                                        ];

                                        if ($role !== 'author') {
                                            \Debug::info('Creating Document Signature for Document \''.$document_reference
                                                .'\', Issue \''.$issue.'\', Role \''.$role.'\', User \''.$user_reference
                                                .'\' from signature code data');
                                        }

                                        $user_key = $user_reference;
                                }
                                if ($user_key === false) {
                                    \Debug::debug($role, $document_reference);
                                    $this->stream->stop();
                                }
                            }
                        } else {
                            \Debug::debug($this->projects[$project_id]['phases'][$variant]['documents'][$document_reference]);
                            $this->stream->stop();
                        }
                    } else {
                        $this->errors['Signatures'][$document_reference] = 'Project '.$project_id
                            .' does not have a document with this reference (L'.__LINE__.')';
                    }
                }
            }
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 2),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors, JSON_PRETTY_PRINT));
        \Storage::put('modules/aegis/import/project_data.json', json_encode($this->projects, JSON_PRETTY_PRINT));
    }
    private function row($row)
    {
        $keys = [
            'ID',
            'Signature-Code',
            'Progressive-number',
            'Role(DOC)',
            'Date',
            'DOC-ID',
            'ISSUE',
            'USER-NICKNAME',
            'ROLE-USER',
        ];
        $row = $row->toArray();
        foreach ($row as &$cell) {
            $cell = trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $cell));
        }
        $row  = array_combine($keys, array_slice($row, 0, count($keys)));
        $data = [
            'company'             => null,
            'date'                => $row['Date'],
            'document_reference'  => $row['DOC-ID'],
            'found'               => false,
            'issue'               => $row['ISSUE'],
            'progressive_number'  => $row['Progressive-number'],
            'role'                => strtolower($row['Role(DOC)']),
            'signature_reference' => $row['Signature-Code'],
            'user_reference'      => strtolower($row['USER-NICKNAME']),
            'user_role'           => $row['ROLE-USER'],
        ];
        if (!($data['company'] = strpos($data['document_reference'], '-') !== false
            ? explode('-', $data['document_reference'])[0]
            : null)
        ) {
            if (!($data['company'] = strpos($data['user_reference'], '/') !== false
                ? explode('/', $data['user_reference'])[0]
                : null)
            ) {
                $data['company'] = strpos($data['document_reference'], '/') !== false
                    ? explode('/', $data['document_reference'])[0]
                    : null;
            }
        }

        if (!$data['date']) {
            $data['date'] = date('Y-m-d H:i:s');
        } else {
            $data['date'] = $this->date_convert($data['date']);
        }

        return $data;
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
}
