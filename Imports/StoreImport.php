<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\CompanyType;
use Modules\AEGIS\Models\Customer;
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
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentApprovalProcessItem;
use Modules\Documents\Models\Group;
use Modules\Documents\Models\UserGroup;
use Spatie\Activitylog\Facades\CauserResolver;

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

        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading data',
        ]);

        $this->approval_processes  = ApprovalProcess::pluck('id', 'name')->toArray();
        $this->categories          = Category::pluck('id', 'name')->toArray();
        $this->companies           = Company::withTrashed()->pluck('id', 'abbreviation')->toArray();
        $this->feedback_list_types = FeedbackListType::pluck('id', 'reference')->toArray();
        $this->job_titles          = JobTitle::pluck('id', 'name')->toArray();
        $this->customers           = Customer::pluck('id', 'name')->toArray();
        $this->types               = Type::pluck('id', 'name')->toArray();
        $this->variant_references  = ProjectVariant::pluck('reference')->toArray();

        $errors        = json_decode(\Storage::get('modules/aegis/import/errors.json'), true);
        $groups        = Group::all();
        $limit_percent = 3;
        $me            = \Auth::user();
        $projects      = json_decode(\Storage::get('modules/aegis/import/project_data.json'), true);
        $users         = User::withTrashed()->get();

        $project_count = count($projects);

        foreach ($groups as $group) {
            $this->groups[$group->name]['id']    = $group->id;
            $this->groups[$group->name]['users'] = $group->user_groups()->pluck('id')->toArray();
        }

        foreach ($users as $user) {
            $meta = $user->getMeta();
            if ($meta->count()) {
                $this->users[$meta['aegis.user-reference']] = [
                    'id'    => $user->id,
                    'email' => $user->email,
                ];
            }
        }

        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing data',
        ]);
        // Loop through Projects
        $i     = 0;
        $limit = min($project_count, ceil($project_count / 100 * $limit_percent));
        if ($project_count > $limit) {
            $message = '----- LIMITING TO FIRST '.number_format($limit).'/'.number_format($project_count).' PROJECTS -----';
            \Debug::critical($message);
            $stream->send([
                'percentage' => 0,
                'message'    => '&nbsp;&nbsp;&nbsp;'.$message,
            ]);
            $projects = array_slice($projects, 0, $limit);
        }
        foreach ($projects as $project_reference => $project) {
            $customer_id   = $this->get_customer($project['customer']);
            $type_id       = $this->get_type($project['type']);

            // Create/Load Project
            $project_model = Project::firstOrNew([
                'reference' => $project_reference,
            ]);
            $is_new_project             = !$project_model->id;
            $project_model->added_by    = $project['added_by'];
            $project_model->company_id  = $this->companies[$project['company']];
            $project_model->description = $project['description'] ?? '';
            $project_model->name        = $project['name'];
            $project_model->scope_id    = $customer_id;
            $project_model->status      = $project['status'];
            $project_model->type_id     = $type_id;
            $project_model->save(['timestamps' => false]);

            if ($is_new_project) {
                CauserResolver::setCauser($me);
                $project_model->log(
                    'messages.added.x-to-y',
                    [
                        'x' => $project_model->reference,
                        'y' => 'dictionary.projects',
                    ]
                );
            }

            // Loop through phases if there are any
            if ($project['phases']) {
                foreach ($project['phases'] as $variant_number => $variant) {
                    // Get reference
                    if ($variant_number === 0) {
                        $reference = $project_model->reference;
                    } else {
                        $reference = $project_model->reference.'/'.str_pad($variant_number, 3, '0', STR_PAD_LEFT);
                    }
                    // Create/Load Variant
                    $project_variant_model = ProjectVariant::firstOrNew([
                        'project_id'     => $project_model->id,
                        'is_default'     => $variant_number === 0 ? true : false,
                        'variant_number' => $variant_number,
                    ]);
                    $is_new_phase                       = !$project_variant_model->id;
                    $project_variant_model->added_by    = $me->id;
                    $project_variant_model->description = $variant['description'];
                    $project_variant_model->name        = $variant['name']
                                                            ?? ($variant_number === 0 ? 'Default' : 'Phase'.$variant_number);
                    $project_variant_model->reference   = $reference;
                    $project_variant_model->save(['timestamps' => false]);

                    if ($is_new_phase) {
                        CauserResolver::setCauser($me);
                        $project_variant_model->log(
                            'aegis::messages.added-project-phase',
                            [
                                'phase'   => $project_variant_model->name,
                                'project' => $project_model->reference,
                            ]
                        );
                    }

                    // Loop through documents if there are any
                    if (!isset($variant['documents'])) {
                        \Debug::emergency($variant);
                        $this->stream->stop();
                        continue;
                    }
                    foreach ($variant['documents'] as $document_reference => $document) {
                        // Get Category and Process
                        $category_id = $this->get_category($document['category'], $document['category_prefix']);
                        $process_id  = $this->get_approval_process($document['category']);
                        // Clear document meta (additional information for AEGIS)
                        $meta = [];
                        // If author role, set
                        if ($document['created_by_role']) {
                            $meta['author_role'] = $this->get_job_title($document['created_by_role']);
                        }
                        // Create/Load Document
                        $document_model = Document::firstOrNew([
                            'name' => trim($document['name']),
                        ]);
                        $is_new                           = $document_model->id ? false : true;
                        $document_model->author_reference = $document['author']['reference'];
                        $document_model->category_id      = $category_id;
                        $document_model->created_by       = $this->get_user($document['created_by']);
                        $document_model->process_id       = $process_id;
                        $document_model->status           = isset($document['submitted_at']) ? 'Approved' : 'In Approval';
                        $document_model->submitted_at     = isset($document['submitted_at']) ? $document['submitted_at'] : null;
                        $document_model->save(['timestamps' => false]);
                        // Log "New Document"
                        if ($is_new) {
                            CauserResolver::setCauser($document_model->created_by);
                            $document_model->log('documents::phrases.created-document', ['document' => $document_model->name]);
                        }
                        // Create/Load Document Variant
                        $variant_model = VariantDocument::firstOrNew([
                            'variant_id'  => $project_variant_model->id,
                            'document_id' => $document_model->id,
                            'issue'       => $document['issue'],
                        ]);
                        $variant_model->created_at = $document['created_at'];
                        $variant_model->reference  = $document_reference;
                        $variant_model->save(['timestamps' => false]);

                        // If FBL
                        if ($document['category_prefix'] === 'FBL') {
                            if ($document['feedback_list']) {
                                if ($document['feedback_list']['type']) {
                                    $meta['feedback_list_type_id'] = $this->get_feedback_list_type(
                                        $document['feedback_list']['type'],
                                        $document['feedback_list']['name'],
                                        $document_reference
                                    );
                                }
                                $meta['final_feedback_list'] = $document['feedback_list']['final'];
                            }
                        }
                        // Save Meta
                        $document_model->setMeta($meta);
                        $document_model->save(['timestamps' => false]);

                        // Loop through Comments if there are any
                        if (!isset($document['comments'])) {
                            \Debug::debug($document);
                            $this->stream->stop();
                        } elseif ($document['comments']) {
                            foreach ($document['comments'] as $comment) {
                                $comment_model = Comment::firstOrNew([
                                    'content'                   => $comment['content'],
                                    'document_approval_item_id' => null,
                                    'document_id'               => $document_model->id,
                                    'user_id'                   => $this->get_user($comment['author']),
                                ]);
                                $comment_model->created_at = $comment['created_at'];
                                $comment_model->updated_at = $comment['updated_at'];
                                $comment_model->save(['timestamps' => false]);

                                $document_model->updated_by = $comment_model->user_id;

                            }
                        }
                        // Loop through approval if there are any
                        if (!$document['approval']) {
                            continue;
                        }
                        foreach ($document['approval'] as $role => $approval_issues) {
                            if (!$approval_issues) {
                                continue;
                            }
                            $approval_process = $document_model->approval_process;
                            $signature        = [
                                'group_id' => $this->get_group($role),
                            ];
                            // Get Approval Stage
                            if ($approval_process->name === 'Archived') {
                                // The first stage will be "Archived"
                                $approval_stage = $approval_process->approval_process_stages->first();
                            } elseif ($approval_process
                                ->approval_process_stages
                                ->where('name', ucwords($role))
                                ->count() === 1
                            ) {
                                // Use the first stage if there's only one
                                $approval_stage = $approval_process
                                    ->approval_process_stages
                                    ->where('name', ucwords($role))
                                    ->first();
                            } else {
                                // If there's more than one stage
                                $role_convert = [
                                    'Report' => [
                                        'Reviewer' => 'Checker',
                                    ],
                                ];
                                // Convert the role then check for stages
                                if (array_key_exists($approval_process->name, $role_convert)
                                    && array_key_exists(ucwords($role), $role_convert[$approval_process->name])
                                ) {
                                    $new_role = $role_convert[$approval_process->name][ucwords($role)];
                                    if ($approval_process->approval_process_stages
                                        ->where('name', $new_role)
                                        ->count() === 1
                                    ) {
                                        $approval_stage = $approval_process
                                            ->approval_process_stages
                                            ->where('name', $new_role)
                                            ->first();
                                    } else {
                                        \Debug::debug([
                                            'name' => $new_role,
                                            'approval_process_stages' => $approval_process->approval_process_stages->count(),
                                        ]);
                                        $this->stream->stop();
                                    }
                                } elseif ($approval_process
                                    ->approval_process_stages
                                    ->where('name', ucwords($role))
                                    ->count() === 0
                                ) {
                                    // If there's no approval process stages
                                    $numbers = [
                                        'Feedback List' => [
                                            'Reviewer' => 0,
                                        ],
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
                                    // If the approval process and role is in $numbers, create the stage and item
                                    if (array_key_exists($approval_process->name, $numbers)
                                        && array_key_exists(ucwords($role), $numbers[$approval_process->name])
                                    ) {
                                        $number = $numbers[$approval_process->name][ucwords($role)];
                                        $stage  = ApprovalProcessStage::firstOrNew([
                                            'approval_process_id' => $approval_process->id,
                                            'name'                => ucwords($role),
                                        ]);
                                        $stage->approvals_until_progressed = 0;
                                        $stage->number                     = $number;
                                        $stage->save(['timestamps' => false]);

                                        ApprovalProcessItem::firstOrCreate([
                                            'approval_stage_id'    => $stage->id,
                                            'required_to_progress' => false,
                                        ]);
                                    } elseif (ucwords($role) !== 'Author') {
                                        \Debug::debug([
                                            'approval process name' => $approval_process->name,
                                            'role'                  => ucwords($role),
                                        ]);
                                        $this->stream->stop();
                                    }
                                } else {
                                    // there's more than one approval process stage
                                    \Debug::debug([
                                        'approval process name' => $approval_process->name,
                                        'role'                  => ucwords($role),
                                    ]);
                                    $this->stream->stop();
                                }
                            }
                            // Get Approval Item
                            if ($approval_stage->approval_process_items->count() === 1) {
                                // If only 1
                                $approval_item = $approval_stage->approval_process_items()->first();
                            } elseif ($approval_stage->approval_process_items->count() === 0) {
                                // If there's none, create the item and group
                                $approval_item = ApprovalProcessItem::firstOrCreate([
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
                            foreach ($approval_issues as $issue => $approval_users) {
                                // if ($role === 'author') {
                                //     \Debug::debug($approval_users);
                                //     $errors['Document Signatures'][$document_reference] = 'Project '.$project_reference
                                //         .', Phase '.$variant_number.', Document '.$document_reference.', Role '.$role
                                //         .' needs processing (L'.__LINE__.')';
                                //     continue;
                                // }
                                foreach ($approval_users as $approval_references) {
                                    foreach ($approval_references as $user_reference => $approval) {
                                        if (!isset($approval['company'])) {
                                            $approval['company'] = $project['company'];
                                        }
                                        if (!array_key_exists('role', $approval)) {
                                            if ($role !== 'author') {
                                                $errors['Document Signatures'][$document_reference] = 'Project '.$project_reference
                                                    .', Phase '.$variant_number.', Document '.$document_reference.', Role '.$role
                                                    .' does not have an assigned role (L'.__LINE__.')';
                                            }
                                            continue;
                                        }
                                        $signature['job_title_id'] = $this->get_job_title($approval['role']);
                                        $signature['user_id']      = $this->get_user($user_reference);

                                        $this->check_user_group($role, $signature['user_id']);

                                        $approval_process_item = DocumentApprovalProcessItem::firstOrNew([
                                            'agent_id'         => $signature['user_id'],
                                            'approval_item_id' => $approval_item->id,
                                            'document_id'      => $document_model->id,
                                            'reference'        => $approval['signature_reference'],
                                        ]);
                                        $approval_process_item->created_at  = $approval['created_at'];
                                        $approval_process_item->status      = $approval['status'];
                                        $approval_process_item->updated_at  = $approval['updated_at'];

                                        // \Debug::debug($project_reference, $document_reference);
                                        $approval_process_item->save(['timestamps' => false]);


                                        if ($approval['comments']) {
                                            $comment = Comment::firstOrNew([
                                                'content'                   => $approval['comments'],
                                                'document_approval_item_id' => $approval_process_item->id,
                                                'document_id'               => $document_model->id,
                                                'user_id'                   => $approval_process_item->agent_id,
                                            ]);
                                            $comment->created_at = $approval['created_at'];
                                            $comment->updated_at = $approval['updated_at'];
                                            $comment->save(['timestamps' => false]);
                                        }

                                        $document_model->updated_by = $approval_process_item->agent_id;

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
                        $document_model->save();
                    }
                }
            } else {
                // Create blank one
                $project_variant_model = ProjectVariant::Create(
                    [
                        'added_by'       => $me->id,
                        'description'    => '',
                        'name'           => 'Default',
                        'project_id'     => $project_model->id,
                        'reference'      => $project_model->reference,
                        'is_default'     => true,
                        'variant_number' => 0,
                    ],
                );
                CauserResolver::setCauser($me);
                $project_variant_model->log(
                    'aegis::messages.added-project-phase',
                    [
                        'phase'   => $project_variant_model->name,
                        'project' => $project_model->reference,
                    ]
                );
            }
            $this->stream->send([
                'percentage' => number_format(($i ++) /  $limit * 100, 2),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($errors, JSON_PRETTY_PRINT));
    }
    private function check_user_group($group, $user_id)
    {
        $group_name = ucwords($group).'s';
        if (!in_array($user_id, $this->groups[$group_name]['users'])) {
            $user_group = UserGroup::firstOrCreate(
                [
                    'group_id' => $this->groups[$group_name]['id'],
                    'user_id'  => $user_id,
                ],
                []
            );
            CauserResolver::setCauser(\Auth::user());
            $user_group->group->log(
                'documents::messages.added-user-to-approval-group',
                [
                    'group' => $user_group->group->name,
                    'user'  => $user_group->user->name,
                ]
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
        $name = trim($name ?? 'Other');
        if (array_key_exists($name, $this->categories)) {
            $category_id = $this->categories[$name];
        } else {
            $category  = Category::firstOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'prefix'              => $prefix ?? 'O',
                    'approval_process_id' => $this->get_approval_process($name),
                ]
            );
            CauserResolver::setCauser(\Auth::user());
            $category->log('messages.added.x-to-y', ['x' => $name, 'y' => 'documents::phrases.approval-groups']);
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
            CauserResolver::setCauser(\Auth::user());
            $fbl_type->log('messages.added.x-to-y', ['x' => $name, 'y' => 'aegis::phrases.feedback-list-types']);
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
            CauserResolver::setCauser(\Auth::user());
            $group_model->log('messages.added.x-to-y', ['x' => $group_name, 'y' => 'documents::phrases.approval-groups']);
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
            CauserResolver::setCauser(\Auth::user());
            $title_model->log('messages.added.x-to-y', ['x' => $title, 'y' => 'aegis::phrases.job-titles']);
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
            $user      = \Auth::user();
            while (Customer::where(['reference' => $reference.$j])->first()) {
                $j++;
            }
            $customer_model = Customer::firstOrCreate(
                [
                    'name' => $customer,
                ],
                [
                    'reference' => strtoupper($reference.$j),
                    'added_by'  => $user->id,
                ]
            );
            $customer_id                = $customer_model->id;
            $this->customers[$customer] = $customer_id;
            CauserResolver::setCauser($user);
            $customer_model->log('messages.added.x-to-y', ['x' => $customer, 'y' => 'dictionary.customers']);
        }
        return $customer_id;
    }
    private function get_type($type)
    {
        if (array_key_exists($type, $this->types)) {
            $type_id = $this->types[$type];
        } else {
            $name       = $type ?? 'Other';
            $user       = \Auth::user();

            $type_model = Type::firstOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'added_by' => $user->id,
                ]
            );
            CauserResolver::setCauser($user);
            $type_model->log('messages.added.x-to-y', ['x' => $name, 'y' => 'aegis::phrases.project-types']);
            foreach ($this->companies as $id) {
                CompanyType::firstOrCreate(
                    [
                        'company_id' => $id,
                        'type_id'    => $type_model->id,
                    ]
                );
            }
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

            $user_emails = array_column($this->users, 'email');

            $new_user_data = [
                'id'    => null,
                'email' => $reference.'@aegisengineering.co.uk',
            ];

            if (($email_position = array_search($new_user_data['email'], $user_emails)) !== false) {
                $user = $this->users[array_keys($this->users)[$email_position]];
            } else {
                $found = false;
                foreach ([
                    'aegis-cert.co.uk',
                    'aegisengineering.co.uk',
                ] as $email_domain) {
                    if ($db_user = User::firstWhere('email', $reference.'@'.$email_domain)) {
                        $this->users[$reference] = [
                            'id'    => $db_user->id,
                            'email' => $db_user->email,
                        ];
                        $found = true;
                        $user  = $this->users[$reference];
                    }
                }
                if (!$found) {
                    $first_name   = ucwords(substr($reference, 0, 1));
                    $last_name    = ucwords(substr($reference, 1));
                    $user         = User::create([
                        'title'      => '',
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                        'email'      => $new_user_data['email'],
                        'status'     => false,
                    ]);
                    CauserResolver::setCauser(\Auth::user());
                    $user->log('messages.added.x-to-y', ['x' => $user->name, 'y' => 'dictionary.users']);

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
                }
            }
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
