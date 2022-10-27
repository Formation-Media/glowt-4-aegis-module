<?php

namespace Modules\AEGIS\Http\Controllers\Stream;

use App\Helpers\SSEStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\AEGIS\Imports\DocumentSignatureImport;
use Modules\AEGIS\Imports\DocumentsImport;
use Modules\AEGIS\Imports\ProjectsImport;
use Modules\AEGIS\Imports\RetryDocumentSignatureImport;
use Modules\AEGIS\Imports\SignatureImport;
use Modules\AEGIS\Imports\StoreImport;
use Modules\AEGIS\Imports\UsersImport;
use Modules\Documents\Models\ApprovalItemGroup;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\ApprovalProcessItem;
use Modules\Documents\Models\ApprovalProcessStage;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\CategoryApprovalProcess;
use Modules\Documents\Models\Group;

class ManagementController extends Controller
{
    private $import_file_path = null;

    public function import(SSEStream $stream, Request $request)
    {
        // $mode = 'collect';
        $mode = 'store';
        // $mode = 'both';
        ini_set('max_execution_time', 0);
        $stream->send([
            'percentage' => 0,
            'message'    => 'Loading required information',
        ]);
        // Prepare blank .JSON
        $this->import_file_path = \Module::getModulePath('AEGIS').'/Resources/files/import/';

        if (in_array($mode, ['both', 'store'])) {
            Schema::disableForeignKeyConstraints();
            foreach ([
                '\App\Models\Activity',

                '\Modules\AEGIS\Models\CompanyType',
                '\Modules\AEGIS\Models\DocumentApprovalItemDetails',
                '\Modules\AEGIS\Models\FeedbackListType',
                '\Modules\AEGIS\Models\JobTitle',
                '\Modules\AEGIS\Models\Project',
                '\Modules\AEGIS\Models\ProjectVariant',
                '\Modules\AEGIS\Models\Scope',
                '\Modules\AEGIS\Models\Type',
                '\Modules\AEGIS\Models\VariantDocument',

                '\Modules\Documents\Models\ApprovalItemGroup',
                '\Modules\Documents\Models\ApprovalProcess',
                '\Modules\Documents\Models\ApprovalProcessItem',
                '\Modules\Documents\Models\ApprovalProcessStage',
                '\Modules\Documents\Models\Category',
                '\Modules\Documents\Models\CategoryApprovalProcess',
                '\Modules\Documents\Models\CategoryApprovalProcessItem',
                '\Modules\Documents\Models\Comment',
                '\Modules\Documents\Models\Document',
                '\Modules\Documents\Models\DocumentApprovalProcessItem',
                '\Modules\Documents\Models\Group',
                '\Modules\Documents\Models\UserGroup',
            ] as $class) {
                $class::truncate();
            }
            Schema::enableForeignKeyConstraints();
        }
        if (in_array($mode, ['both', 'collect'])) {
            \Storage::delete([
                'modules/aegis/import/retry_document_signatures.json',
                'modules/aegis/import/projects_and_documents.json',
                'modules/aegis/import/projects_and_document_signatures.json',
                'modules/aegis/import/project_data.json',
            ]);
            \Storage::put('modules/aegis/import/errors.json', json_encode([]));
            \Storage::put('modules/aegis/import/projects.json', json_encode([]));
        }
        $steps = [
            'users' => [
                'users.xlsx',
            ],
            'document_types' => [
                'processes.php',
            ],
        ];
        if (in_array($mode, ['both', 'collect'])) {
            $steps = array_merge(
                $steps,
                [
                    'projects' => [
                        'acs/ALL_PROJECTS.xlsx',
                        'aes/ALL_PROJECTS.xlsx',
                    ],
                    'documents' => [
                        'acs/DOCUMENTS.xlsx',
                        'aes/DOCUMENTS.xlsx',
                    ],
                    'document_signatures' => [
                        'acs/DOCUMENTS_Signature.xlsx',
                        'aes/DOCUMENTS_Signature.xlsx',
                    ],
                    'signatures' => [
                        'acs/SIGNATURE_CODE.xlsx',
                        'aes/SIGNATURE_CODE.xlsx',
                    ],
                ]
            );
        }
        foreach ($steps as $method => $files) {
            $name = ucwords(str_replace('_', ' ', $method));
            $stream->send([
                'percentage' => 0,
                'message'    => 'Processing files for '.$name,
            ]);
            foreach ($files as $i => $file) {
                $stream->send([
                    'message' => '&nbsp;&nbsp;&nbsp;File #'.($i + 1).' ('.$file.')',
                ]);
                $this->$method($stream, $file);
                // if ($method === 'document_signatures') {
                //     new RetryDocumentSignatureImport($stream);
                // }
            }
            $stream->send([
                'percentage' => 100,
                'message'    => 'Finished processing files for '.$name,
            ]);
        }
        if (in_array($mode, ['both', 'store'])) {
            // $this->store($stream);
        }
        $stream->stop([
            'message'    => 'Finished importing data',
            'percentage' => 100,
            // 'redirect'   => '/a/m/AEGIS/management/import-errors',
            'redirect'   => '/a/m/AEGIS/management/import-testing',
        ]);
    }
    private function users($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(new UsersImport($stream), $this->import_file($file));
    }
    private function document_types($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Creating Approval Groups and Processes',
        ]);
        include $this->import_file($file);
        foreach ($processes as $data) {
            if (count($data['stages'])) {
                foreach ($data['stages'] as $stage) {
                    foreach ($stage['items'] as $item) {
                        foreach ($item['groups'] as $group) {
                            Group::firstOrCreate(
                                [
                                    'name' => $group,
                                ],
                                []
                            );
                        }
                    }
                }
            }
        }
        $groups = Group::pluck('id', 'name')->toArray();
        foreach ($processes as $approval_process_name => $data) {
            $approval_process = ApprovalProcess::firstOrCreate(
                [
                    'name' => $approval_process_name,
                ],
                []
            );
            if (count($data['stages'])) {
                foreach ($data['stages'] as $i => $stage) {
                    $approval_stage = ApprovalProcessStage::firstOrCreate(
                        [
                            'approval_process_id' => $approval_process->id,
                            'name'                => $stage['name'],
                        ],
                        [
                            'approvals_until_progressed' => $stage['approvals'],
                            'number'                     => $i + 1,
                        ]
                    );
                    foreach ($stage['items'] as $item) {
                        $approval_process_item = ApprovalProcessItem::firstOrCreate(
                            [
                                'approval_stage_id'    => $approval_stage->id,
                                'required_to_progress' => $item['required'],
                            ],
                            []
                        );
                        foreach ($item['groups'] as $group) {
                            ApprovalItemGroup::firstOrCreate(
                                [
                                    'approval_item_id' => $approval_process_item->id,
                                    'group_id'         => $groups[$group],
                                ],
                                []
                            );
                        }
                    }
                }
            }
            if (count($data['types'])) {
                foreach ($data['types'] as $i => $type) {
                    $category = Category::firstOrCreate(
                        [
                            'name' => $type,
                        ],
                        [
                            'prefix' => 'O',
                        ]
                    );
                    CategoryApprovalProcess::firstOrCreate(
                        [
                            'category_id' => $category->id,
                            'process_id'  => $approval_process->id,
                        ]
                    );
                }
            }
        }
    }
    private function projects($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(new ProjectsImport($stream), $this->import_file($file));
    }
    private function documents($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(new DocumentsImport($stream), $this->import_file($file));
    }
    private function document_signatures($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(new DocumentSignatureImport($stream), $this->import_file($file));
    }
    private function signatures($stream, $file)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(new SignatureImport($stream), $this->import_file($file));
    }
    private function store($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => 'Storing Data',
        ]);
        new StoreImport($stream);
    }

    private function import_file($file)
    {
        return $this->import_file_path.$file;
    }
}
