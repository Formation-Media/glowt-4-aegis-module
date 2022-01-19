<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\Document;

class DocumentsImport implements ToCollection
{
    private $stream;

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
        $approval_processes = ApprovalProcess::pluck('id', 'name')->toArray();
        $categories         = Category::pluck('id', 'name');
        $documents          = [];
        $process_lookup     = [];
        $projectless        = [];
        $projects           = Project::orderBy('reference')->pluck('id', 'reference');
        $variant_documents  = [];
        $variants           = [];
        $users              = $this->users;
        include \Module::getModulePath('AEGIS').'/Resources/files/import/processes.php';
        foreach ($processes as $process_name => $process) {
            foreach ($process['types'] as $type) {
                $process_lookup[$type] = $process_name;
            }
        }
        $processes = array_combine(array_keys($processes), array_column($processes, 'types'));
        foreach (Document::select('id', 'name', 'created_at')->get() as $document) {
            $documents[(string) $document->created_at][$document->name] = $document->id;
        }
        foreach (ProjectVariant::select('id', 'project_id', 'variant_number')->get() as $variant) {
            $variants[$variant['project_id']][$variant['variant_number']] = $variant['id'];
        }
        foreach (VariantDocument::select('id', 'variant_id', 'document_id')->with(['project_variant'])->get() as $variant_document) {
            $variant_documents[$variant_document->document_id][$variant_document->project_variant->variant_number]
                = $variant_document->id;
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !isset($row[1])) {
                continue;
            }
            $project_reference = $this->row($row, 'PROJECT-IDENTIFICATION');
            if (!isset($projects[$project_reference])) {
                $projectless[] = $row[0];
                continue;
            }
            $author  = $this->row($row, 'AUTHOR');
            $type    = $this->row($row, 'DOC-TYPE');
            $variant = $this->row($row, 'VARIANT NUMBER');
            if (array_key_exists($type, $process_lookup)) {
                $process = $type;
            } else {
                $process = 'Archived';
            }
            if (array_key_exists($type, $categories)) {
                $category_id = $categories[$type];
            } else {
                $reference = $this->row($row, 'DOC-LETTER');
                $category  = Category::firstOrCreate(
                    [
                        'name' => $type ?? 'Other',
                    ],
                    [
                        'prefix'              => $type ? $reference : 'O',
                        'approval_process_id' => $approval_processes[$process_lookup[$process]],
                    ]
                );
                $category_id       = $category->id;
                $categories[$type] = $category_id;
            }
            $date = $this->row($row, 'CREATION-DATE');
            $date = $date ? date('Y-m-d', strtotime('+'.($date - 1).' days', strtotime('1900-01-01'))).' '
                .gmdate('H:i:s', $this->row($row, 'CRE-TIME') * 60 * 60 * 24) : date('2021-12-21 17:28:00');
            $document_name = $this->row($row, 'DOC-NAME');
            $document_name = substr($document_name, 0, 191);
            $lower_author  = strtolower($author);
            $user          = $users[$lower_author];
            if (!isset($user['id'])) {
                $first_name   = ucwords(substr($author, 0, 1));
                $last_name    = ucwords(substr($author, 1));
                $user_details = [
                    'email'      => $user['email'],
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                ];
                if (!($user = User::where('email', $user_details['email'])->first())) {
                    $user_details['password'] = Hash::make(microtime());
                    $user_details['status']   = 0;
                    $user                     = User::create($user_details);
                    $this->stream->send([
                        'message' => '&nbsp;&nbsp;&nbsp;Created User \''.$first_name.' '.$last_name.'\'',
                    ]);
                }
                $users[$lower_author]['id'] = $user->id;
            }
            if (isset($documents[$date][$document_name])) {
                $document_id = $documents[$date][$document_name];
            } else {
                $document = Document::firstOrCreate(
                    [
                        'created_at' => $date,
                        'name'       => $document_name,
                    ],
                    [
                        'category_id' => $category_id,
                        'created_by'  => $users[$lower_author]['id'],
                        'status'      => 'Approved',
                    ]
                );
                $document_id = $document->id;
            }
            if (!isset($variant_documents[$document_id][$variant])) {
                VariantDocument::firstOrCreate(
                    [
                        'variant_id'  => $variants[$projects[$project_reference]][$variant],
                        'document_id' => $document_id,
                    ],
                    [
                        'created_at' => $date,
                        'reference'  => $this->row($row, 'DOC-IDENTIFICATION'),
                    ]
                );
            }
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        if ($projectless) {
            $this->stream->send([
                'percentage' => 100,
                'message'    => '&nbsp;&nbsp;&nbsp;<span style="color:red;">Projectless Documents:</span>'
                    .'<ol><li><span style="color:red;">'.implode('</span></li><li><span style="color:red;">', $projectless)
                    .'</span></li></ol>',
            ]);
        }
    }
    private function row($row, $key)
    {
        $keys = [
            'DOC-IDENTIFICATION',
            'VARIANT NUMBER',
            'VARIANT NAME',
            'DOC-NAME',
            'DOC-TYPE',
            'CREATION-DATE',
            'AUTHOR',
            'DOC-DESCRIPTION',
            'PROJECT-IDENTIFICATION',
            'PROJECT NAME',
            'DOC-PROGR-NUM',
            'PROVA',
            'ISSUE',
            'CRE-TIME',
            'DOC-LETTER',
            'FBL TYPE',
            'PRE-TITLE FBL',
        ];
        return $row[array_search($key, $keys)];
    }
}
