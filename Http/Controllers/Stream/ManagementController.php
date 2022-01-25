<?php

namespace Modules\AEGIS\Http\Controllers\Stream;

use App\Helpers\SSEStream;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Imports\DocumentSignatureImport;
use Modules\AEGIS\Imports\DocumentsImport;
use Modules\AEGIS\Imports\ProjectsImport;
use Modules\AEGIS\Imports\SignatureImport;
use Modules\Documents\Models\ApprovalItemGroup;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\ApprovalProcessItem;
use Modules\Documents\Models\ApprovalProcessStage;
use Modules\Documents\Models\Group;

class ManagementController extends Controller
{
    private $users;

    public function import(SSEStream $stream, Request $request)
    {
        ini_set('max_execution_time', 60 * 60);
        $stream->send([
            'percentage' => 0,
            'message'    => 'Loading required information',
        ]);
        $this->users  = [
            'aallen'       => ['email' => 'a.allen@aegiseng.co.uk'],
            'aalston'      => ['email' => 'a.alston@aegis-cert.co.uk'],
            'abatters'     => ['email' => 'andybatters@aegisengineering.co.uk'],
            'abrodniewski' => ['email' => 'andybrod1@gmail.com'],
            'acarson'      => ['email' => 'a.carson@aegiseng.co.uk'],
            'acolver'      => ['email' => 'andycolver@aegisengineering.co.uk'],
            'adesemery'    => ['email' => 'adesemery@aegisengineering.co.uk'],  // Auto-email
            'aharvey'      => ['email' => 'andrew.harvey@aegis-cert.co.uk'],
            'ahines'       => ['email' => 'ahines@aegisengineering.co.uk'],     // Auto-email
            'ahunt'        => ['email' => 'ahunt@aegisengineering.co.uk'],      // Auto-email
            'ajackson'     => ['email' => 'a.jackson@aegiseng.co.uk'],
            'akhan'        => ['email' => 'asmakhan@aegisengineering.co.uk'],
            'akitchen'     => ['email' => 'Alan.Kitchen2@gmail.com'],
            'amoors'       => ['email' => 'allie@humanimpactsolutions.co.uk'],
            'anair'        => ['email' => 'a.nair@aegiseng.co.uk'],
            'aroberts'     => ['email' => 'a.roberts@aegis-cert.co.uk'],
            'babdalenus'   => ['email' => 'burhan.abdalenus@aegisengineering.co.uk'],
            'bcardwell'    => ['email' => 'bcardwell@aegisengineering.co.uk'],  // Auto-email
            'bhenson'      => ['email' => 'b.henson@aegiseng.co.uk'],
            'bmack'        => ['email' => 'bethanmack@aegisengineering.co.uk'],
            'bmckendrick'  => ['email' => 'b.mckendrick@aegiseng.co.uk'],
            'bmorley'      => ['email' => 'benmorley@aegisengineering.co.uk'],
            'bpearce'      => ['email' => 'balvinpearce@aegisengineering.co.uk'],
            'byan'         => ['email' => 'byan@aegisengineering.co.uk'],       // Auto-email
            'cbeales'      => ['email' => 'c.beales@aegiseng.co.uk'],
            'choare'       => ['email' => 'chrishoare@aegisengineering.co.uk'],
            'cmusisi'      => ['email' => 'cmusisi@aegisengineering.co.uk'],    // Auto-email
            'cplace'       => ['email' => 'colinplace@aegisengineering.co.uk'],
            'csquires'     => ['email' => 'kajengineering@gmail.com'],
            'ddiana'       => ['email' => 'd.diana@aegiseng.co.uk'],
            'dford'        => ['email' => 'daveford.jnr@ntlworld.com'],
            'dhitchcock'   => ['email' => 'dhitchcock@aegisengineering.co.uk'], // Auto-email
            'dmould'       => ['email' => 'dmould@aegisengineering.co.uk'],     // Auto-email
            'doldroyd'     => ['email' => 'd.oldroyd@aegiseng.co.uk'],
            'dsteenson'    => ['email' => 'dansteenson@aegisengineering.co.uk'],
            'dsubedi'      => ['email' => 'd.subedi@aegis-cert.co.uk'],
            'dthomson'     => ['email' => 'technical@frangusltd.co.uk'],
            'ebrundle'     => ['email' => 'e.brundle@aegiseng.co.uk'],
            'edavison'     => ['email' => 'edavison@aegisengineering.co.uk'],   // Auto-email
            'ekalogeraki'  => ['email' => 'e.kalogeraki@aegiseng.co.uk'],
            'gastin'       => ['email' => 'gavinastin@aegisengineering.co.uk'],
            'gbrown'       => ['email' => 'g.brown@aegiseng.co.uk'],
            'ghiggs'       => ['email' => 'ghiggs@aegisengineering.co.uk'],     // Auto-email
            'gsivaswamy'   => ['email' => 'gopalsivaswamy@aegisengineering.co.uk'],
            'hmclean'      => ['email' => 'hughmclean@aegisengineering.co.uk'],
            'imackinnon'   => ['email' => 'ianworldtour@hotmail.com'],
            'iwright'      => ['email' => 'i.wright@aegiseng.co.uk'],
            'jallenden'    => ['email' => 'jallenden@aegisengineering.co.uk'],  // Auto-email
            'jcourt'       => ['email' => 'jcourt@aegisengineering.co.uk'],     // Auto-email
            'jeaton'       => ['email' => 'j.eaton@aegiseng.co.uk'],
            'jjohnson'     => ['email' => 'j.johnson@aegiseng.co.uk'],
            'jtraynor'     => ['email' => 'j.traynor@aegiseng.co.uk'],
            'kbilbey'      => ['email' => 'kbilbey@aegisengineering.co.uk'],    // Auto-email
            'kbott'        => ['email' => 'ken.bott@mbrail.co.uk'],
            'kchedumbarum' => ['email' => 'k.chedumbarum@aegiseng.co.uk'],
            'kellaby'      => ['email' => 'kellaby@aegisengineering.co.uk'],    // Auto-email
            'kkelly'       => ['email' => 'k.kelly@aegis-cert.co.uk'],
            'kruff'        => ['email' => 'k.ruff@aegis-cert.co.uk'],
            'kstepniewska' => ['email' => 'k.stepniewska@aegiseng.co.uk'],
            'kuthayanan'   => ['email' => 'k.uthayanan@aegiseng.co.uk'],
            'lcapogna'     => ['email' => 'l.capogna@aegis-cert.co.uk'],
            'ldangelo'     => ['email' => 'ldangelo@aegisengineering.co.uk'],   // Auto-email
            'lhawketts'    => ['email' => 'lhawketts@aegisengineering.co.uk'],  // Auto-email
            'lwood'        => ['email' => 'l.wood@aegiseng.co.uk'],
            'melliott'     => ['email' => 'm.elliott@aegis-cert.co.uk'],
            'mmccool'      => ['email' => 'm.mccool@aegiseng.co.uk'],
            'mramosgarcia' => ['email' => 'marioramosgarcia@aegisengineering.co.uk'],
            'mrobinson'    => ['email' => 'michael.robinson20@btinternet.com'],
            'msmit'        => ['email' => 'msmit@aegisengineering.co.uk'],      // Auto-email
            'mwesterman'   => ['email' => 'martinwesterman@aegisengineering.co.uk'],
            'narran'       => ['email' => 'acezap@ntlworld.com'],
            'nwiles'       => ['email' => 'nathanwiles@aegisengineering.co.uk'],
            'oal-jumaili'  => ['email' => 'othaman@hotmail.co.uk'],
            'odawson'      => ['email' => 'o.dawson@aegiseng.co.uk'],
            'pbebbington'  => ['email' => 'p.bebbington@aegiseng.co.uk'],
            'pbutler'      => ['email' => 'info@radicalinternational.ltd.uk'],
            'pcourt'       => ['email' => 'p.court@aegis-cert.co.uk'],
            'pdallman'     => ['email' => 'pdallman@aegisengineering.co.uk'],   // Auto-email
            'pelwell'      => ['email' => 'philelwell@aegisengineering.co.uk'],
            'perwin'       => ['email' => 'consultengrail@yahoo.co.uk'],
            'pgregory'     => ['email' => 'petergreggory@aegisengineering.co.uk'],
            'pknott'       => ['email' => 'philippaknott@aegisengineering.co.uk'],
            'pproctor'     => ['email' => 'p.proctor@aegiseng.co.uk'],
            'prose'        => ['email' => 'prose@aegisengineering.co.uk'],      // Auto-email
            'pwatkins'     => ['email' => 'p.watkins@aegiseng.co.uk'],
            'rbell'        => ['email' => 'rbell@aegisengineering.co.uk'],      // Auto-email
            'rmartin'      => ['email' => 'r.martin@aegiseng.co.uk'],
            'rperry'       => ['email' => 'ryanperry@aegisengineering.co.uk'],
            'rwebster'     => ['email' => 'rosemarywebster@aegisengineering.co.uk'],
            'rwells'       => ['email' => 'richardwells@aegisengineering.co.uk'],
            'sbarrett'     => ['email' => 's.barrett@aegis-cert.co.uk'],
            'scrowther'    => ['email' => 's.crowther@aegiseng.co.uk'],
            'semson'       => ['email' => 's.emson@aegiseng.co.uk'],
            'sgossling'    => ['email' => 's.gossling@aegis-cert.co.uk'],
            'ssulkowski'   => ['email' => 's.sulkowski@aegiseng.co.uk'],
            'sturner'      => ['email' => 'steve@railengconsult.co.uk'],
            'tsteel'       => ['email' => 't.steel@aegiseng.co.uk'],
            'tweeraban'    => ['email' => 'tawinanweeraban@aegisengineering.co.uk'],
            'twoof'        => ['email' => 't.woof@aegiseng.co.uk'],
            'utest'        => ['email' => 'utest@aegisengineering.co.uk'],      // Auto-email
            'vadams'       => ['email' => 'vickiadams@aegisengineering.co.uk'],
        ];
        $user_by_email = User::pluck('id', 'email');
        foreach ($user_by_email as $email => $id) {
            foreach ($this->users as &$user) {
                if ($email == $user['email']) {
                    $user['id'] = $id;
                    break;
                }
            }
        }
        $steps = [
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
        $stream->stop([
            'percentage' => 100,
            'message'    => 'Finished importing data',
        ]);
        exit;
    }
    private function document_types($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Creating Approval Groups',
        ]);
        foreach ([
            'Archived',
        ] as $group) {
            Group::firstOrCreate([
                'name' => $group,
            ]);
        }
        $groups = Group::pluck('id', 'name')->toArray();
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Creating Approval Processes',
        ]);
        include \Module::getModulePath('AEGIS').'/Resources/files/import/processes.php';
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
    private function documents($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new DocumentsImport($stream, $this->users),
            \Module::getModulePath('AEGIS').'/Resources/files/import/documents.xlsx'
        );
    }
    private function projects($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new ProjectsImport($stream, $this->users),
            \Module::getModulePath('AEGIS').'/Resources/files/import/projects.xlsx'
        );
    }
    private function document_signatures($stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading import file',
        ]);
        \Excel::import(
            new DocumentSignatureImport($stream, $this->users),
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
            new SignatureImport($stream, $this->users),
            \Module::getModulePath('AEGIS').'/Resources/files/import/signatures.xlsx'
        );
    }
}
