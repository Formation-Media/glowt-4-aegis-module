<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Comment;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\Documents\Models\Group;
use Modules\Documents\Models\UserGroup;

class SignatureImport implements ToCollection
{
    private $documents = [];
    private $row;
    private $stream;
    private $users;

    public function __construct(SSEStream $stream, $users, $method)
    {
        $this->method = $method;
        $this->stream = $stream;
        $this->users  = $users;
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
        $companies = Company::withTrashed()->pluck('id', 'abbreviation');
        $groups    = Group::all();
        foreach ($groups as $group) {
            $group_data[$group->name]['id']    = $group->id;
            $group_data[$group->name]['users'] = $group->user_groups()->pluck('id')->toArray();
        }
        $invalid_documents = [];
        $job_titles        = JobTitle::pluck('id', 'name')->toArray();
        $temp_data         = json_decode(
            file_get_contents(\Module::getModulePath('AEGIS').'/Resources/files/import/temp_data.json'),
            true
        );
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !array_filter($row->toArray())) {
                continue;
            }

            $this->row = $row;

            $approval_item       = null;
            $approval_stage      = null;
            $date                = $this->column('Date');
            $document_issue      = $this->column('ISSUE');
            $document_reference  = $this->column('DOC-ID');
            $invalid_documents   = [];
            $job_title           = $this->column('ROLE-USER');
            $role                = strtolower($this->column('Role(DOC)'));
            $signature_reference = $this->column('Signature-Code');
            $user_reference      = strtolower($this->column('USER-NICKNAME'));
            \Log::debug([
                __FILE__ => __LINE__,
                $user_reference
            ]);
            continue;

            $company    = strpos($signature_reference, '-') !== false ? explode('-', $signature_reference)[0] : null;
            $group_name = ucwords($role).'s';
            $stage_name = ucwords($role);
// Check there's a corresponding document
            if (!($document = VariantDocument
                ::with([
                    'document',
                    'document.category',
                    'document.category.approval_process',
                    'document.category.approval_process.approval_process_stages',
                ])
                ->where([
                    'reference' => $document_reference,
                    'issue'     => $document_issue,
                ])
                ->first()
            )) {
                $invalid_documents[] = $document_reference.' issue '.$document_issue.' - Document Not Found';
                continue;
            }
            $approval_process = $document->document->category->approval_process;
            $approval_stages  = $approval_process->approval_process_stages();
// Get Company
            if (!$company) {
                $company = explode('/', $document_reference)[0];
                if (!$company) {
                    $invalid_documents[] = $document_reference.' issue '.$document_issue.' - Company Not Found ('.$company.')';
                    continue;
                }
            }
// Generate Signature Reference if it doesn't exist
            if (!$signature_reference) {
                $signature_reference = $company.'-'.$user_reference.'-0';
            }
            if ($role === 'author') {
                // If Author, we're just interested in comments
                if (array_key_exists($document->id, $temp_data) && $temp_data[$document->id]['comments']) {
                    $signature_data              = $temp_data[$document->id];
                    $signature_data['reference'] = $signature_reference;
                } else {
                    // There are no author comments so there's nothing else to do.
                    continue;
                }
            } else {
                // Get approval process and stage
                if ($approval_process->name === 'Archived') {
                    $approval_stage = $approval_stages->first();
                } elseif ($approval_stages->where('name', $stage_name)->count() === 1) {
                    $approval_stage = $approval_stages->where('name', $stage_name)->first();
                } else {
                    $invalid_documents[] = $document_reference.' issue '.$document_issue.' - Approval Stage Not Found';
                    \Log::debug([
                        __FILE__ => __LINE__,
                        $row
                    ]);
                    continue;
                }
// We have the approval stage, now get the approval item
                if ($approval_stage->approval_process_items->count() === 1) {
                    $approval_item = $approval_stage->approval_process_items()->first();
                } else {
                    \Log::debug([
                        __FILE__ => __LINE__,
                        $approval_stage->approval_process_items
                    ]);
                    break;
                }
// Collate Signature Data
                if (array_key_exists($document->id, $temp_data)) {
                    $signature_data                     = $temp_data[$document->id];
                    $signature_data['reference']        = $signature_reference;
                    $signature_data['approval_item_id'] = $approval_item->id;
                } elseif ($date) {
                    $date = $this->date_convert('Date');
                    if (!array_key_exists($user_reference, $this->users)) {
                        $first_name   = ucwords(substr($user_reference, 0, 1));
                        $last_name    = ucwords(substr($user_reference, 1));
                        $user_details = [
                            'email'      => $user_reference.'@aegisengineering.co.uk',
                            'first_name' => $first_name,
                            'last_name'  => $last_name,
                        ];
                        if (!($user = User::where('email', $user_details['email'])->first())) {
                            $user_details['password'] = Hash::make(microtime());
                            $user_details['status']   = 0;
                            $user                     = User::create($user_details);
                            $this->stream->send([
                                'percentage' => round(($i + 1) / count($rows) * 100, 1),
                                'message'    => '&nbsp;&nbsp;&nbsp;Created User \''.$first_name.' '.$last_name.'\'',
                            ]);
                        }
                        $this->users[$user_reference]['id'] = $user->id;
                        $user = $this->users[$user_reference];
                    } else {
                        $user = $this->users[$user_reference];
                    }
                    if (!array_key_exists('id', $user)) {
                        \Log::debug([
                            __FILE__ => __LINE__,
                            $row,
                            $user
                        ]);
                        break;
                    } else {
                        $signature_data = [
                            'agent_id'         => $user['id'],
                            'approval_item_id' => $approval_item->id,
                            'created_at'       => $date,
                            'comments'         => [],
                            'document_id'      => $document->document->id,
                            'reference'        => $signature_reference,
                            'status'           => 'Approved',
                            'updated_at'       => $date,
                        ];
                    }
                }
// Collate Approval Item Details
                if (!array_key_exists($job_title, $job_titles)) {
                    $title = JobTitle::create([
                        'name'   => $job_title,
                        'status' => 0,
                    ]);
                    $job_titles[$job_title] = $title->id;
                }
                $approval_item_details = [
                    'approval_item_id' => $signature_data['approval_item_id'],
                    'company_id'       => $companies[$company],
                    'job_title_id'     => $job_titles[$job_title],
                ];
// Error on null of anything
                foreach ([
                    'approval_item_details',
                    'signature_data',
                ] as $dataset) {
                    foreach ($$dataset as $key => $value) {
                        if (!in_array($key, ['comments']) && !$value) {
                            \Log::debug([
                                __FILE__ => __LINE__,
                                'Attribute "'.$key.'" is in "'.$dataset.'" not set.',
                            ]);
                            break;
                        }
                    }
                }
// Process non-author stuff
                // Create group if it's missing
                if (!array_key_exists($group_name, $group_data)) {
                    $group = Group::firstOrCreate(
                        [
                            'name' => $group_name,
                        ],
                        []
                    );
                    $group_data[$group_name] = [
                        'id'    => $group->id,
                        'users' => [],
                    ];
                }
                UserGroup::firstOrCreate(
                    [
                        'group_id' => $group->id,
                        'user_id'  => $signature_data['agent_id'],
                    ],
                    []
                );
                $group_data[$group_name]['users'][] = $signature_data['agent_id'];
                \Log::debug([
                    __FILE__ => __LINE__,
                    $signature_data
                ]);
                $process_item = DocumentApprovalProcessItem::firstOrCreate(
                    [
                        'agent_id'         => $signature_data['agent_id'],
                        'approval_item_id' => $signature_data['approval_item_id'],
                        'document_id'      => $signature_data['document_id'],
                        'reference'        => $signature_data['reference'],
                    ],
                    [
                        'created_at' => $signature_data['created_at'],
                        'status'     => $signature_data['status'],
                        'updated_at' => $signature_data['updated_at'],
                    ]
                );
            }
// So just to confirm, here, we have the appropriate $process_item here, along with any comments
            $comments = $signature_data['comments'];
            if ($comments) {
                foreach ($comments as $comment) {
                    // If the user_id is not numeric then it's a reference which we need to populate
                    if (!is_numeric($comment['user_id'])) {
                        $user_reference = strtolower($comment['user_id']);
                        if (!array_key_exists($user_reference, $this->users)) {
                            $first_name   = ucwords(substr($user_reference, 0, 1));
                            $last_name    = ucwords(substr($user_reference, 1));
                            $user_details = [
                                'email'      => $user_reference.'@aegisengineering.co.uk',
                                'first_name' => $first_name,
                                'last_name'  => $last_name,
                            ];
                            if (!($user = User::where('email', $user_details['email'])->first())) {
                                $user_details['password'] = Hash::make(microtime());
                                $user_details['status']   = 0;
                                $user                     = User::create($user_details);
                                $this->stream->send([
                                    'percentage' => round(($i + 1) / count($rows) * 100, 1),
                                    'message'    => '&nbsp;&nbsp;&nbsp;Created User \''.$first_name.' '.$last_name.'\'',
                                ]);
                            }
                            $this->users[$user_reference]['id'] = $user->id;
                            $comment['user_id'] = $user->id;
                        } else {
                            $comment['user_id'] = $this->users[$user_reference]['id'];
                        }
                    }
                    if (!array_key_exists('user_id', $comment)) {
                        \Log::debug([
                            __FILE__ => __LINE__,
                            $row,
                            $comment
                        ]);
                        break;
                    } else {
                        Comment::firstOrCreate(
                            [
                                'content'                   => $comment['content'],
                                'document_approval_item_id' => $process_item->id ?? null,
                                'document_id'               => $document->document->id,
                                'user_id'                   => $comment['user_id'],
                            ],
                            [
                                'created_at' => $signature_data['created_at'],
                                'updated_at' => $signature_data['updated_at'],
                            ]
                        );
                    }
                }
            }
// Get rid of the auhors now, we've already handled all we need to from them, no offence!
            if ($role === 'author') {
                continue;
            }
            $approval_item_details['approval_item_id'] = $process_item->id;
            DocumentApprovalItemDetails::firstOrCreate(
                $approval_item_details,
                []
            );
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        if ($invalid_documents) {
            $invalid_documents = array_filter($invalid_documents);
        }
        $this->stream->send([
            'percentage' => 100,
            'message'    => count($invalid_documents)
                ? '&nbsp;&nbsp;&nbsp;<span style="color:red;">Could not import signature for documents:</span>'
                    .'<ol><li><span style="color:red;">'.implode('</span></li><li><span style="color:red;">', $invalid_documents)
                    .'</span></li></ol>'
                : '',
        ]);
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
    private function method_2($rows)
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
            $document_issue      = $this->column('ISSUE');
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

            if ($date) {
                foreach ($this->projects as $project_reference => $project) {
                    foreach ($project['variants'] as $variant_number => $variant) {
                        if (array_key_exists('documents', $variant)
                            && array_key_exists($document_reference, $variant['documents'])
                            && array_key_exists($document_issue, $variant['documents'][$document_reference])
                            && array_key_exists('approval', $variant['documents'][$document_reference][$document_issue])
                            && array_key_exists($role, $variant['documents'][$document_reference][$document_issue]['approval'])
                            && array_key_exists(
                                $user_reference,
                                $variant['documents'][$document_reference][$document_issue]['approval'][$role]
                            )
                        ) {
                            $user = $this->projects[$project_reference]['variants'][$variant_number]['documents']
                                [$document_reference][$document_issue]['approval'][$role][$user_reference];

                            $date                        = $this->date_convert('Date');
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

                            $this->projects[$project_reference]['variants'][$variant_number]['documents'][$document_reference]
                                [$document_issue]['approval'][$role][$user_reference] = $user;

                            $row['found'] = true;
                            // Skip the rest of the projects and documents we've assigned the data where we can
                            break 2;
                        }
                    }
                }
                if (!$row['found']) {
                    $this->errors['Signatures'][$document_reference] = 'Appropriate Document Signature could not be found';
                }
            } else {
                $this->errors['Signatures'][$document_reference] = 'Signature for role ('.$role.') does not have a valid date';
            }
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/project_data.json', json_encode($this->projects));
    }
}
