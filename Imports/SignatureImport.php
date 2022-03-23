<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SignatureImport implements ToCollection
{
    private $row;
    private $stream;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $signature_references = [];
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $this->errors = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        $this->projects = json_decode(
            \Storage::get('modules/aegis/import/projects_and_document_signatures.json'),
            true
        );
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Updating data with document signatures',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $this->row           = $row;
            $row['found']        = false;
            $date                = $this->column('Date');
            $document_reference  = $this->column('DOC-ID');
            $issue               = $this->column('ISSUE');
            $progressive_number  = $this->column('Progressive-number');
            $role                = strtolower($this->column('Role(DOC)'));
            $signature_reference = $this->column('Signature-Code');
            $user_reference      = strtolower($this->column('USER-NICKNAME'));

            if ($role === 'author') {
                continue;
            }

            $company = strpos($signature_reference, '-') !== false
                ? explode('-', $signature_reference)[0]
                : null;
            if (!$company) {
                $company = strpos($signature_reference, '/') !== false
                ? explode('/', $signature_reference)[0]
                : null;
            }
            if (!$company) {
                $company = strpos($document_reference, '/') !== false
                ? explode('/', $document_reference)[0]
                : null;
            }

            if (!$signature_reference) {
                $signature_reference = $company.'-'.$user_reference.'-'.$progressive_number;
            }

            foreach ($this->projects as $project_reference => $project) {
                foreach ($project['variants'] as $variant_number => $variant) {
                    if (array_key_exists('documents', $variant)
                        && array_key_exists($document_reference, $variant['documents'])
                        && array_key_exists('approval', $variant['documents'][$document_reference])
                        && array_key_exists($role, $variant['documents'][$document_reference]['approval'])
                    ) {
                        $variant['documents'][$document_reference]['issue'] = max(
                            $variant['documents'][$document_reference]['issue'],
                            array_keys($variant['documents'][$document_reference]['approval'][$role])
                        );

                        $user_key  = false;
                        if (array_key_exists($issue, $variant['documents'][$document_reference]['approval'][$role])) {
                            foreach ($variant['documents'][$document_reference]['approval'][$role][$issue] as $key => $item) {
                                if (array_search($user_reference, array_keys($item)) !== false) {
                                    $user_key = $key;
                                    break;
                                }
                            }
                        } else {
                            foreach ($variant['documents'][$document_reference]['approval'][$role] as $approval_issue => $items) {
                                foreach ($items as $key => $item) {
                                    if (array_search($user_reference, array_keys($item)) !== false) {
                                        $user_key  = $key;
                                        break 2;
                                    }
                                }
                            }
                        }

                        if ($user_key === false) {
                            continue;
                        }

                        $user = $variant['documents'][$document_reference]['approval'][$role][$issue][$user_key][$user_reference];

                        if (!$date) {
                            $date = date('Y-m-d H:i:s');
                        } else {
                            $date = $this->date_convert('Date');
                        }

                        $user['company']             = $company;
                        $user['signed_date']         = $date;
                        $user['increment']           = $progressive_number;
                        $user['signature_reference'] = $signature_reference;
                        $user['created_at']          = date(
                            'Y-m-d H:i:s',
                            min(strtotime($date), strtotime($user['created_at']))
                        );
                        $user['updated_at'] = date(
                            'Y-m-d H:i:s',
                            max(strtotime($date), strtotime($user['updated_at']))
                        );
                        $this->projects[$project_reference]['variants'][$variant_number]['documents'][$document_reference]['approval']
                            [$role][$issue][$user_key][$user_reference] = $user;

                        $row['found'] = true;
                        // Skip the rest of the projects and documents we've assigned the data where we can
                        break 2;
                    }
                }
            }
            if (!$row['found']) {
                $old_document_id = explode('/', $document_reference)[1];
                if (strlen($old_document_id) > 3 && $old_document_id < 9900) {
                    $exploded_document_reference = explode('/', $document_reference);
                    $project_id                  = implode('/', array_slice($exploded_document_reference, 0, 2));
                    $variant                     = false;

                    if (array_key_exists($project_id, $this->projects)) {
                        if (array_key_exists('variants', $this->projects[$project_id])) {
                            foreach ($this->projects[$project_id]['variants'] as $variant_number => $variant_details) {
                                if (array_key_exists('documents', $variant_details)
                                    && array_key_exists($document_reference, $variant_details['documents'])
                                ) {
                                    $variant = $variant_number;
                                    break;
                                }
                            }
                            if ($variant !== false) {
                                if (array_key_exists(
                                    'approval',
                                    $this->projects[$project_id]['variants'][$variant]['documents'][$document_reference]
                                )) {
                                    if (array_key_exists(
                                        $role,
                                        $this->projects[$project_id]['variants'][$variant]['documents'][$document_reference]
                                            ['approval']
                                    )) {
                                        $issue_key = false;
                                        $user_key  = false;
                                        if (array_key_exists($issue, $this->projects[$project_id]['variants'][$variant]['documents']
                                            [$document_reference]['approval'][$role])
                                        ) {
                                            $this->projects[$project_id]['variants'][$variant]['documents'][$document_reference]
                                                ['issue'] = max(
                                                    $this->projects[$project_id]['variants'][$variant]['documents']
                                                        [$document_reference]['issue'],
                                                    array_keys($this->projects[$project_id]['variants'][$variant]['documents']
                                                            [$document_reference]['approval'][$role])
                                                );

                                            foreach ($this->projects[$project_id]['variants'][$variant]['documents']
                                                [$document_reference]['approval'][$role][$issue] as $key => $item
                                            ) {
                                                if (array_search($user_reference, array_keys($item)) !== false) {
                                                    $issue_key = $issue;
                                                    $user_key  = $key;
                                                    break;
                                                }
                                            }
                                        } else {
                                            foreach ($this->projects[$project_id]['variants'][$variant]['documents']
                                                [$document_reference]['approval'][$role] as $approval_issue => $items
                                            ) {
                                                foreach ($items as $key => $item) {
                                                    if (array_search($user_reference, array_keys($item)) !== false) {
                                                        $issue_key = $approval_issue;
                                                        $user_key  = $key;
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        if ($user_key !== false) {
                                            // This was caught above

                                            // \Log::debug([
                                            //     __FILE__ => __LINE__,
                                            //     $row,
                                            //     $this->projects[$project_id]['variants'][$variant]['documents']
                                            //         [$document_reference]['approval'][$role][$issue_key][$user_key][$user_reference],
                                            // ]);
                                            // $this->stream->stop();
                                            // exit;
                                        } else {
                                            $this->errors['Signatures'][$document_reference]
                                                = 'Signature does not have a matching document signature matching role ('.$role
                                                    .') and user ('.$user_reference.')';
                                        }
                                    }
                                } else {
                                    \Log::debug([
                                        __FILE__ => __LINE__,
                                        $this->projects[$project_id]['variants'][$variant]['documents'][$document_reference],
                                    ]);
                                    $this->stream->stop();
                                    exit;
                                }
                            } else {
                                $this->errors['Signatures'][$document_reference] = 'Project '.$project_id
                                    .' does not have a document with this reference';
                            }
                        } else {
                            $this->errors['Signatures'][$document_reference] = 'Project '.$project_id.' does not have any variants.';
                        }
                    } else {
                        $this->errors['Signatures'][$document_reference] = 'Project '.$project_id.' does not exist.';
                    }
                }
            }
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/project_data.json', json_encode($this->projects));
    }
    private function column($key)
    {
        $keys = [
            'Signature-Code',
            'Progressive-number',
            'Role(DOC)',
            'Date',
            'DOC-ID',
            'ISSUE',
            'USER-NICKNAME',
            'ROLE-USER',
        ];
        return $this->row[array_search($key, $keys)];
    }
    private function date_convert($date, $time = null)
    {
        $date = $this->column($date);
        $time = $time ? $this->column($time) : 0;
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
