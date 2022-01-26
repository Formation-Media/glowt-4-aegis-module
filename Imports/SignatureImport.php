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
    private $row;
    private $stream;
    private $users;

    public function __construct(SSEStream $stream, $users)
    {
        $this->stream = $stream;
        $this->users  = $users;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $companies         = Company::withTrashed()->pluck('id', 'abbreviation');
        $groups            = Group::all();
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
            if ($i === 0) {
                continue;
            }
            $this->row           = $row;
            $signature_reference = $this->column('Signature-Code');
            $company             = strpos($signature_reference, '-') !== false ? explode('-', $signature_reference)[0] : null;
            $document_reference  = $this->column('DOC-ID');
            $user_reference      = strtolower($this->column('USER-NICKNAME'));
            if (!$company) {
                $company = explode('/', $document_reference)[0];
                if (!$company) {
                    \Log::debug([
                        __FILE__ => __LINE__,
                        $document_reference => $signature_reference,
                    ]);
                    break;
                }
            }
            if (!$signature_reference) {
                $signature_reference = $company.'-'.$user_reference.'-0';
            }
            $job_title = $this->column('ROLE-USER');
            if (!($document = VariantDocument
                ::with([
                    'document',
                    'document.category',
                    'document.category.approval_process',
                    'document.category.approval_process.approval_process_stages',
                ])
                ->where('reference', $document_reference)
                ->first()
            )) {
                $invalid_documents[] = $document_reference;
                continue;
            }
            $approval_process = $document->document->category->approval_process;
            $approval_stages  = $approval_process->approval_process_stages();
            $date             = $this->column('Date');
            $role             = strtolower($this->column('Role(DOC)'));
            if ($approval_stages->count() === 1) {
                $approval_stage = $approval_stages->first();
            } else {
                $refined_approval_stages = $approval_stages->where('name', ucwords($role));
                if ($refined_approval_stages->count() === 1) {
                    $approval_stage = $refined_approval_stages->first();
                } elseif ($refined_approval_stages->count() > 1) {
                    \Log::debug([
                        __FILE__ => __LINE__,
                        $row,
                        $refined_approval_stages->get()
                    ]);
                    break;
                } else {
                    if ($approval_process->name === 'Report') {
                        if ($role === 'reviewer') {
                            $role = 'checker';
                        } else {
                            \Log::debug([
                                __FILE__ => __LINE__,
                                $row,
                                $approval_process->toArray()
                            ]);
                            break;
                        }
                    } elseif ($approval_process->name === 'Proposal') {
                        if ($role === 'reviewer') {
                            $role = 'approver';
                        } else {
                            \Log::debug([
                                __FILE__ => __LINE__,
                                $row,
                                $approval_process->toArray()
                            ]);
                            break;
                        }
                    } else {
                        \Log::debug([
                            __FILE__ => __LINE__,
                            'signature' => $signature_reference,
                            'document'  => $document_reference,
                            $approval_process->toArray(),
                        ]);
                        break;
                    }
                    $refined_approval_stages = $approval_process->approval_process_stages()->where('name', ucwords($role));
                    if ($refined_approval_stages->count() === 1) {
                        $approval_stage = $refined_approval_stages->first();
                    } elseif ($refined_approval_stages->count() > 1) {
                        \Log::debug([
                            __FILE__ => __LINE__,
                            $row,
                            $refined_approval_stages->first()
                        ]);
                        break;
                    } else {
                        \Log::debug([
                            __FILE__ => __LINE__,
                            $row,
                            $approval_process->toArray()
                        ]);
                        break;
                    }
                }
            }
            if ($approval_stage->approval_process_items()->count() === 1) {
                $approval_item = $approval_stage->approval_process_items()->first();
            } else {
                \Log::debug([
                    __FILE__ => __LINE__,
                    $approval_stage->approval_process_items
                ]);
                break;
            }
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
            $comments     = $signature_data['comments'];
            if (!array_key_exists(ucwords($role.'s'), $group_data)) {
                $group = Group::firstOrCreate(
                    [
                        'name' => ucwords($role.'s'),
                    ],
                    []
                );
                $group_data[ucwords($role.'s')] = [
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
            $group_data[ucwords($role.'s')]['users'][] = $signature_data['agent_id'];
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
            if ($comments) {
                foreach ($comments as $comment) {
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
                    } else {
                        Comment::firstOrCreate(
                            [
                                'content'                   => $comment['content'],
                                'document_approval_item_id' => $process_item->id,
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
}
