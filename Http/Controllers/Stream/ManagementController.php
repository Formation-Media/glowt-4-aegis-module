<?php

namespace Modules\AEGIS\Http\Controllers\Stream;

use App\Helpers\SSEStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Imports\DocumentSignatureImport;
use Modules\AEGIS\Imports\DocumentsImport;
use Modules\AEGIS\Imports\ProjectsImport;
use Modules\AEGIS\Imports\SignatureImport;
use Modules\AEGIS\Imports\StoreImport;
use Modules\AEGIS\Imports\UsersImport;
use Modules\Documents\Models\ApprovalItemGroup;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\ApprovalProcessItem;
use Modules\Documents\Models\ApprovalProcessStage;
use Modules\Documents\Models\Group;

class ManagementController extends Controller
{
    public function import(SSEStream $stream, Request $request)
    {
        ini_set('max_execution_time', 0);
        $stream->send([
            'percentage' => 0,
            'message'    => 'Loading required information',
        ]);
        /*
            TRUNCATE `m_aegis_document_approval_item_details`;
            TRUNCATE `m_aegis_projects`;
            TRUNCATE `m_aegis_project_variants`;
            TRUNCATE `m_aegis_variant_documents`;
            TRUNCATE `m_documents_approval_items`;
            TRUNCATE `m_documents_approval_items_groups`;
            TRUNCATE `m_documents_approval_processes`;
            TRUNCATE `m_documents_approval_stages`;
            TRUNCATE `m_documents_categories`;
            TRUNCATE `m_documents_category_approval_items`;
            TRUNCATE `m_documents_comments`;
            TRUNCATE `m_documents_documents`;
            TRUNCATE `m_documents_documents_approval_items`;
            TRUNCATE `m_documents_groups`;
            TRUNCATE `m_documents_meta`;
            TRUNCATE `m_documents_user_groups`;
        */
        $steps = [
            'users' => [
                'Processing Users&hellip;',
                'Finished Processing Users.',
            ],
            'document_types' => [
                'Processing Document Types&hellip;',
                'Finished Processing Document Types.',
            ],
            'projects' => [
                'Processing Projects&hellip;',
                'Finished Processing Projects.',
            ],
            'documents' => [
                'Processing Documents&hellip;',
                'Finished Processing Documents.',
            ],
            'document_signatures' => [
                'Processing Document Signatures&hellip;',
                'Finished Processing Document Signatures.',
            ],
            'signatures' => [
                'Processing Signatures&hellip;',
                'Finished Processing Signatures.',
            ],
            'store' => [
                'Storing Data&hellip;',
                'Finished Storing Data.',
            ],
        ];
        foreach ($steps as $method => $messages) {
            $stream->send([
                'percentage' => 0,
                'message'    => $messages[0],
            ]);
            $this->$method($stream);
            $stream->send([
                'percentage' => 100,
                'message'    => $messages[1],
            ]);
        }
        $this->store($stream);
        $stream->stop([
            'message'    => 'Finished importing data',
            'percentage' => 100,
            'redirect'   => '/a/m/AEGIS/management/import-errors',
        ]);
    }
    private function users($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new UsersImport($stream),
            \Module::getModulePath('AEGIS').'/Resources/files/import/users.xlsx'
        );
    }
    private function document_types($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Creating Approval Groups and Processes',
        ]);
        include \Module::getModulePath('AEGIS').'/Resources/files/import/processes.php';
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
        }
    }
    private function projects($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new ProjectsImport($stream),
            \Module::getModulePath('AEGIS').'/Resources/files/import/projects.xlsx'
        );
    }
    private function documents($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new DocumentsImport($stream),
            \Module::getModulePath('AEGIS').'/Resources/files/import/documents.xlsx'
        );
    }
    private function document_signatures($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new DocumentSignatureImport($stream),
            \Module::getModulePath('AEGIS').'/Resources/files/import/document-signatures.xlsx'
        );
    }
    private function signatures($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new SignatureImport($stream),
            \Module::getModulePath('AEGIS').'/Resources/files/import/signatures.xlsx'
        );
    }
    private function store($stream)
    {
        new StoreImport($stream);
    }
}
