<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Document;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Type;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\ApprovalItemGroup;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\ApprovalProcessItem;
use Modules\Documents\Models\ApprovalProcessStage;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\Comment;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\Documents\Models\Group;
use Modules\Documents\Models\UserGroup;

class StoreImport
{
    private $approval_processes;
    private $approval_process_lookup;
    private $categories;
    private $groups;
    private $job_titles;
    private $customers;
    private $stream;
    private $types;
    private $user_references;
    private $users;
    private $variant_references;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;

        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);

        $this->approval_processes  = ApprovalProcess::pluck('id', 'name')->toArray();
        $this->categories          = Category::pluck('id', 'name')->toArray();
        $this->companies           = Company::withTrashed()->pluck('id', 'abbreviation')->toArray();
        $this->feedback_list_types = FeedbackListType::pluck('id', 'reference')->toArray();
        $this->job_titles          = JobTitle::pluck('id', 'name')->toArray();
        $this->customers           = Customer::pluck('id', 'name')->toArray();
        $this->types               = Type::pluck('id', 'name')->toArray();
        $this->variant_references  = ProjectVariant::pluck('reference')->toArray();

        $companies         = Company::withTrashed()->pluck('id', 'abbreviation')->toArray();
        $errors            = json_decode(\Storage::get('modules/aegis/import/errors.json'), true);
        $existing_projects = Project::pluck('id', 'reference')->toArray();
        $groups            = Group::all();
        $projects          = json_decode(\Storage::get('modules/aegis/import/project_data.json'), true);
        $users             = User::withTrashed()->get();

        foreach ($groups as $group) {
            $this->groups[$group->name]['id']    = $group->id;
            $this->groups[$group->name]['users'] = $group->user_groups()->pluck('id')->toArray();
        }

        foreach ($users as $user) {
            $meta = $user->getMeta();
            if ($meta->count()) {
                $this->users[$meta['aegis.import-reference']] = [
                    'id'    => $user->id,
                    'email' => $user->email,
                ];
            }
        }

        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing data',
        ]);
        $i = 0;
        foreach ($projects as $project_reference => $project) {
            if (!in_array($project_reference, $existing_projects)) {
                $project_name  = $project['name'];
                $project_type  = $project['type'];

                // Insert Project
                if (strlen($project_name) > 191) {
                    $project_name = substr($project_name, 0, 188).'...';
                }

                $customer_id   = $this->get_customer($project['customer']);
                $type_id       = $this->get_type($project['type']);
                $project_model = Project::firstOrCreate(
                    [
                        'reference' => $project_reference,
                    ],
                    [
                        'added_by'    => $project['added_by'],
                        'company_id'  => $companies[$project['company']],
                        'description' => $project['description'] ?? '',
                        'name'        => $project_name,
                        'scope_id'    => $customer_id,
                        'type_id'     => $type_id,
                    ],
                );
                // Project Variant
                if ($project['variants']) {
                    foreach ($project['variants'] as $variant_number => $variant) {
                        $reference             = $this->get_variant_reference($variant_number);
                        $project_variant_model = ProjectVariant::firstOrCreate(
                            [
                                'project_id'     => $project_model->id,
                                'is_default'     => $variant_number === 0 ? true : false,
                                'variant_number' => $variant_number,
                            ],
                            [
                                'added_by'    => \Auth::id(),
                                'description' => $variant['description'],
                                'name'        => $variant['name'] ?? ($variant_number === 0 ? 'Default' : 'Variant '.$variant_number),
                                'reference'   => $reference,
                            ],
                        );
                        if (!isset($variant['documents'])) {
                            \Log::debug([
                                __FILE__ => __LINE__,
                                $variant
                            ]);
                            break 2;
                        } elseif ($variant['documents']) {
                            foreach ($variant['documents'] as $document_reference => $document) {
                                $category_id = $this->get_category($document['category'], $document['category_prefix']);
                                try {
                                    $document_model = Document::firstOrCreate(
                                        [
                                            'created_at' => $document['created_at'],
                                            'name'       => $document['name'],
                                        ],
                                        [
                                            'category_id' => $category_id,
                                            'created_by'  => $this->get_user($document['created_by']),
                                            'status'      => 'Approved',
                                        ]
                                    );
                                } catch (\Exception $e) {
                                    \Log::debug([
                                        __FILE__ => __LINE__,
                                        $document['name'],
                                        $e->getMessage(),
                                    ]);
                                    $this->stream->stop();
                                }

                                $variant_model = VariantDocument::firstOrCreate(
                                    [
                                        'variant_id'  => $project_variant_model->id,
                                        'document_id' => $document_model->id,
                                        'issue'       => $document['issue'],
                                    ],
                                    [
                                        'created_at' => $document['created_at'],
                                        'reference'  => $document_reference,
                                    ]
                                );

                                if ($document['category_prefix'] === 'FBL') {
                                    if ($document['feedback_list']) {
                                        $meta = [];
                                        if ($document['feedback_list']['type']) {
                                            $meta['feedback_list_type_id'] = $this->get_feedback_list_type(
                                                $document['feedback_list']['type'],
                                                $document['feedback_list']['name'],
                                                $document_reference
                                            );
                                        }
                                        $meta['final_feedback_list'] = $document['feedback_list']['final'];
                                        $document_model->setMeta($meta);
                                        $document_model->save();
                                    }
                                }
                                if (!isset($document['comments'])) {
                                    \Log::debug([
                                        __FILE__ => __LINE__,
                                        $document
                                    ]);
                                    break 3;
                                } elseif ($document['comments']) {
                                    foreach ($document['comments'] as $comment) {
                                        Comment::firstOrCreate(
                                            [
                                                'content'                   => $comment['content'],
                                                'document_approval_item_id' => null,
                                                'document_id'               => $document_model->id,
                                                'user_id'                   => $this->get_user($comment['author']),
                                            ],
                                            [
                                                'created_at' => $comment['created_at'],
                                                'updated_at' => $comment['updated_at'],
                                            ]
                                        );
                                    }
                                }
                                if ($document['approval']) {
                                    foreach ($document['approval'] as $role => $approval_users) {
                                        $approval_process = $document_model->category->approval_process;

                                        $signature['group_id'] = $this->get_group($role);

                                        // Get Approval Stage
                                        if ($approval_process->name === 'Archived') {
                                            $approval_stage = $approval_process->approval_process_stages->first();
                                        } elseif ($approval_process->approval_process_stages
                                            ->where('name', ucwords($role))
                                            ->count() === 1
                                        ) {
                                            $approval_stage = $approval_process->approval_process_stages
                                                ->where('name', ucwords($role))
                                                ->first();
                                        } else {
                                            $role_convert = [
                                                'Report' => [
                                                    'Reviewer' => 'Checker',
                                                ],
                                            ];
                                            if (array_key_exists($approval_process->name, $role_convert)) {
                                                if (array_key_exists(ucwords($role), $role_convert[$approval_process->name])) {
                                                    $new_role = $role_convert[$approval_process->name][ucwords($role)];
                                                    if ($approval_process->approval_process_stages
                                                        ->where('name', $new_role)
                                                        ->count() === 1
                                                    ) {
                                                        $approval_stage = $approval_process->approval_process_stages
                                                            ->where('name', $new_role)
                                                            ->first();
                                                    } else {
                                                        \Log::debug([
                                                            __FILE__ => __LINE__,
                                                            'name'   => $new_role,
                                                            $approval_process->approval_process_stages->count(),
                                                        ]);
                                                        break 4;
                                                    }
                                                }
                                            } elseif ($approval_process->approval_process_stages
                                                ->where('name', ucwords($role))
                                                ->count() === 0
                                            ) {
                                                $numbers = [
                                                    'Letter / Memo' => [
                                                        'Reviewer' => 0,
                                                    ],
                                                    'Proposal' => [
                                                        'Reviewer' => 0,
                                                    ],
                                                    'Report' => [
                                                        'Assessor' => 2,
                                                    ],
                                                ];
                                                if (array_key_exists($approval_process->name, $numbers)
                                                    && array_key_exists(ucwords($role), $numbers[$approval_process->name])
                                                ) {
                                                    $number = $numbers[$approval_process->name][ucwords($role)];
                                                    $stage  = ApprovalProcessStage::firstOrCreate(
                                                        [
                                                            'approval_process_id' => $approval_process->id,
                                                            'name'                => ucwords($role),
                                                        ],
                                                        [
                                                            'approvals_until_progressed' => 0,
                                                            'number'                     => $number,
                                                        ]
                                                    );
                                                    \Log::debug([
                                                        __FILE__ => __LINE__,
                                                        $approval_process->approval_process_stages
                                                            ->where('name', ucwords($role))
                                                            ->count(),
                                                        $stage->toArray()
                                                    ]);
                                                    \Log::debug([
                                                        __FILE__ => __LINE__,
                                                        ApprovalProcessItem::class,
                                                        [
                                                            'approval_stage_id'    => $stage->id,
                                                            'required_to_progress' => false,
                                                        ]
                                                    ]);
                                                    break 4;
                                                } else {
                                                    \Log::debug([
                                                        __FILE__ => __LINE__,
                                                        $approval_process->name,
                                                        ucwords($role),
                                                    ]);
                                                    break 4;
                                                }
                                            } else {
                                                \Log::debug([
                                                    __FILE__ => __LINE__,
                                                    $approval_process->name,
                                                    ucwords($role)
                                                ]);
                                                break 4;
                                            }
                                        }

                                        // Get Approval Item
                                        if ($approval_stage->approval_process_items->count() === 1) {
                                            $approval_item = $approval_stage->approval_process_items()->first();
                                        } elseif ($approval_stage->approval_process_items->count() === 0) {
                                            $approval_item = ApprovalProcessItem::create([
                                                'approval_stage_id'    => $approval_stage->id,
                                                'required_to_progress' => false,
                                            ]);
                                            ApprovalItemGroup::firstOrCreate(
                                                [
                                                    'approval_item_id' => $approval_item->id,
                                                    'group_id'         => $this->get_group($role),
                                                ]
                                            );
                                        }

                                        foreach ($approval_users as $user_reference => $approval) {
                                            if (!isset($approval['signed_date'])) {
                                                continue;
                                            }
                                            $signature['job_title_id'] = $this->get_job_title($approval['role']);
                                            $signature['user_id']      = $this->get_user($user_reference);

                                            \Log::debug([
                                                __FILE__ => __LINE__,

                                            ]);

                                            $this->check_user_group($role, $signature['user_id']);

                                            $approval_process_item = DocumentApprovalProcessItem::firstOrCreate(
                                                [
                                                    'agent_id'         => $signature['user_id'],
                                                    'approval_item_id' => $approval_item->id,
                                                    'document_id'      => $document_model->id,
                                                    'reference'        => $approval['signature_reference'],
                                                ],
                                                [
                                                    'created_at' => $approval['created_at'],
                                                    'status'     => $approval['status'],
                                                    'updated_at' => $approval['updated_at'],
                                                ]
                                            );

                                            if ($approval['comments']) {
                                                Comment::firstOrCreate(
                                                    [
                                                        'content'                   => $approval['comments'],
                                                        'document_approval_item_id' => $approval_process_item->id,
                                                        'document_id'               => $document_model->id,
                                                        'user_id'                   => $approval_process_item->agent_id,
                                                    ],
                                                    [
                                                        'created_at' => $approval['created_at'],
                                                        'updated_at' => $approval['updated_at'],
                                                    ]
                                                );
                                            }
                                            DocumentApprovalItemDetails::firstOrCreate(
                                                [
                                                    'approval_item_id' => $approval_process_item->id,
                                                    'company_id'       => $this->get_company($approval['company']),
                                                    'job_title_id'     => $this->get_job_title($approval['role']),
                                                ],
                                                []
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    \Log::debug([
                        __FILE__ => __LINE__,
                        $project_model->reference
                    ]);
                    break;
                }
            } else {
                \Log::debug([
                    __FILE__ => __LINE__,
                    $project
                ]);
                break;
            }
            $this->stream->send([
                'percentage' => round(($i ++) / count($projects) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($errors));
    }
    private function check_user_group($group, $user_id)
    {
        $group_name = ucwords($group).'s';
        if (!in_array($user_id, $this->groups[$group_name]['users'])) {
            UserGroup::firstOrCreate(
                [
                    'group_id' => $this->groups[$group_name]['id'],
                    'user_id'  => $user_id,
                ],
                []
            );
            $this->groups[$group_name]['users'][] = $user_id;
        }
    }
    private function get_approval_process($category)
    {
        if (!$this->approval_process_lookup) {
            include \Module::getModulePath('AEGIS').'/Resources/files/import/processes.php';
            foreach ($processes as $process_name => $process) {
                foreach ($process['types'] as $type) {
                    $this->approval_process_lookup[$type] = $process_name;
                }
            }
        }
        if (array_key_exists($category, $this->approval_process_lookup)) {
            $process = $category;
        } else {
            $process = 'Archived';
        }
        return $this->approval_processes[$this->approval_process_lookup[$process]];
    }
    private function get_category($name, $prefix)
    {
        if (array_key_exists($name, $this->categories)) {
            $category_id = $this->categories[$name];
        } else {
            $category  = Category::firstOrCreate(
                [
                    'name' => $name ?? 'Other',
                ],
                [
                    'prefix'              => $prefix ?? 'O',
                    'approval_process_id' => $this->get_approval_process($name ?? 'Other'),
                ]
            );
            $this->categories[$name] = $category->id;
        }
        return $this->categories[$name];
    }
    private function get_company($abbreviation)
    {
        return $this->companies[$abbreviation];
    }
    private function get_feedback_list_type($reference, $name, $document)
    {
        if (!array_key_exists($reference, $this->feedback_list_types)) {
            $fbl_type = FeedbackListType::firstOrCreate(
                [
                    'reference' => $reference,
                ],
                [
                    'name' => $name,
                ]
            );
            $this->feedback_list_types[$reference] = $fbl_type->id;
        }
        return $this->feedback_list_types[$reference];
    }
    private function get_group($group)
    {
        $group_name = ucwords($group).'s';
        if (!array_key_exists($group_name, $this->groups)) {
            $group_model = Group::firstOrCreate(
                [
                    'name' => $group_name,
                ],
                []
            );
            $this->groups[$group_name] = [
                'id'    => $group_model->id,
                'users' => [],
            ];
        }
        return $this->groups[$group_name]['id'];
    }
    private function get_job_title($title)
    {
        if (!array_key_exists($title, $this->job_titles)) {
            $title_model = JobTitle::create([
                'name'   => $title,
                'status' => 0,
            ]);
            $this->job_titles[$title] = $title_model->id;
        }
        return $this->job_titles[$title];
    }
    private function get_customer($customer)
    {
        if (array_key_exists($customer, $this->customers)) {
            $customer_id = $this->customers[$customer];
        } else {
            $j         = 1;
            $reference = substr(str_replace(' ', '', $customer), 0, 3);
            while (Customer::where(['reference' => $reference.$j])->first()) {
                $j++;
            }
            $customer_model = Customer::firstOrCreate(
                [
                    'name' => $customer,
                ],
                [
                    'reference' => strtoupper($reference.$j),
                    'added_by'  => \Auth::id(),
                ]
            );
            $customer_id                = $customer_model->id;
            $this->customers[$customer] = $customer_id;
        }
        return $customer_id;
    }
    private function get_type($type)
    {
        if (array_key_exists($type, $this->types)) {
            $type_id = $this->types[$type];
        } else {
            $name       = $type ?? 'Other';
            $type_model = Type::firstOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'added_by' => \Auth::id(),
                ]
            );
            $type_id            = $type_model->id;
            $this->types[$name] = $type_id;
        }
        return $type_id;
    }
    private function get_user($reference)
    {
        if (!array_key_exists($reference, $this->users)) {
            if (!$this->user_references) {
                $this->user_references = \DB::table('users_meta')->where('key', 'aegis.user-reference')->pluck('value');
            }
            $new_user_data = [
                'id'    => null,
                'email' => $reference.'@aegisengineering.co.uk',
            ];
            $first_name   = ucwords(substr($reference, 0, 1));
            $last_name    = ucwords(substr($reference, 1));
            $user         = User::create([
                'title'      => '',
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $new_user_data['email'],
                'status'     => false,
            ]);

            $i              = 1;
            $user_reference = $first_name.ucwords(substr($last_name, 0, 1));

            while (in_array($user_reference.$i, $this->user_references->toArray())) {
                $i++;
            }
            $user->setMeta([
                'aegis.type'             => 1,
                'aegis.import-reference' => $reference,
                'aegis.user-reference'   => $user_reference.$i,
            ]);
            $user->roles()->sync([config('roles.by_name.core.staff')]);
            $user->save();
            $new_user_data['id']     = $user->id;
            $this->users[$reference] = $new_user_data;
            $this->stream->send([
                'message' => '&nbsp;&nbsp;&nbsp;Created User \''.$first_name.' '.$last_name.'\'',
            ]);
        } else {
            $user = $this->users[$reference];
        }
        return $user['id'];
    }
    private function get_variant_reference($variant_number)
    {
        $j         = 1;
        $reference = substr(str_replace(' ', '', $variant_number), 0, 3).'-';
        while (in_array($reference.$j, $this->variant_references)
            || ProjectVariant::where(['reference' => $reference.$j])->count()
        ) {
            $j++;
        }
        $reference = strtoupper($reference.$j);
        $this->variant_references[] = $reference.$j;
        return $reference;
    }
}
