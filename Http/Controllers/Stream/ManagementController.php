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
    private $import_file_path = null;

    public function import(SSEStream $stream, Request $request)
    {
        ini_set('max_execution_time', 0);
        $stream->send([
            'percentage' => 0,
            'message'    => 'Loading required information',
        ]);
        // Prepare blank .JSON
        $this->import_file_path = \Module::getModulePath('AEGIS').'/Resources/files/import/';
        /*
            TRUNCATE `m_aegis_document_approval_item_details`;
            TRUNCATE `m_aegis_feedback_list_types`;
            TRUNCATE `m_aegis_job_titles`;
            TRUNCATE `m_aegis_projects`;
            TRUNCATE `m_aegis_project_variants`;
            TRUNCATE `m_aegis_scopes`;
            TRUNCATE `m_aegis_types`;
            TRUNCATE `m_aegis_variant_documents`;
            TRUNCATE `m_documents_approval_items`;
            TRUNCATE `m_documents_approval_items_groups`;
            TRUNCATE `m_documents_approval_processes`;
            TRUNCATE `m_documents_approval_stages`;
            TRUNCATE `m_documents_categories`;
            TRUNCATE `m_documents_category_approval_items`;
            TRUNCATE `m_documents_category_approval_processes`;
            TRUNCATE `m_documents_comments`;
            TRUNCATE `m_documents_documents`;
            TRUNCATE `m_documents_documents_approval_items`;
            TRUNCATE `m_documents_groups`;
            TRUNCATE `m_documents_meta`;
            TRUNCATE `m_documents_user_groups`;
        */
        \Storage::delete([
            // 'modules/aegis/import/errors.json',
            // 'modules/aegis/import/projects.json',
            // 'modules/aegis/import/projects_and_documents.json',
            'modules/aegis/import/projects_and_document_signatures.json',
            'modules/aegis/import/project_data.json',
        ]);
        \Storage::put('modules/aegis/import/projects.json', json_encode([]));
        $steps = [
            // 'users' => [
            //     'users.xlsx',
            // ],
            // 'document_types' => [
            //     'processes.php',
            // ],
            // 'projects' => [
            //     'acs/ALL_PROJECTS.xlsx',
            //     'aes/ALL_PROJECTS.xlsx',
            // ],
            // 'documents' => [
            //     'acs/DOCUMENTS.xlsx',
            //     'aes/DOCUMENTS.xlsx',
            // ],
            'document_signatures' => [
                'acs/DOCUMENTS_Signature.xlsx',
                'aes/DOCUMENTS_Signature.xlsx',
            ],
            // 'signatures' => [
            //     'acs/SIGNATURE_CODE.xlsx',
            //     'aes/SIGNATURE_CODE.xlsx',
            // ]
        ];
        foreach ($steps as $method => $files) {
            $name = ucwords(str_replace('_', ' ', $method));
            $stream->send([
                'percentage' => 0,
                'message'    => 'Processing files for '.$name,
            ]);
            foreach ($files as $i => $file) {
                $stream->send([
                    'message' => '&nbsp;&nbsp;&nbsp;File #'.($i + 1),
                ]);
                $this->$method($stream, $file);
            }
            $stream->send([
                'percentage' => 100,
                'message'    => 'Finished processing files for '.$name,
            ]);
        }
        // $this->store($stream);
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
        new StoreImport($stream);
    }

    private function import_file($file)
    {
        return $this->import_file_path.$file;
    }
}
