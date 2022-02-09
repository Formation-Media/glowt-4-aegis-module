<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\FeedbackListType;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\ApprovalProcess;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\Document;

class DocumentsImport implements ToCollection
{
    private $errors;
    private $method;
    private $projects;
    private $row;
    private $stream;

    public function __construct(SSEStream $stream, $users, $method)
    {
        $this->method = $method;
        $this->stream = $stream;
        $this->users  = $users;
    }
    public function collection(Collection $rows)
    {
        if ($this->method === 2) {
            $this->method_2($rows);
            return;
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $approval_processes  = ApprovalProcess::pluck('id', 'name')->toArray();
        $categories          = Category::pluck('id', 'name');
        $documents           = [];
        $feedback_list_types = FeedbackListType::pluck('id', 'reference')->toArray();
        $process_lookup      = [];
        $projectless         = [];
        $projects            = Project::orderBy('reference')->pluck('id', 'reference');
        $variant_documents   = [];
        $variants            = [];
        $users               = $this->users;
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

            $this->row = $row;

            $project_reference = $this->column('PROJECT-IDENTIFICATION');
            if (!isset($projects[$project_reference])) {
                $projectless[] = $row[0];
                continue;
            }
            $author             = $this->column('AUTHOR');
            $issue              = $this->column('ISSUE');
            $feedback_list_type = $this->column('FBL TYPE');
            $type               = $this->column('DOC-TYPE');
            $variant            = $this->column('VARIANT NUMBER');
            if (array_key_exists($type, $process_lookup)) {
                $process = $type;
            } else {
                $process = 'Archived';
            }
            if (array_key_exists($type, $categories)) {
                $category_id = $categories[$type];
            } else {
                $reference = $this->column('DOC-LETTER');
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
            $date = $this->column('CREATION-DATE');
            $date = $date ? date('Y-m-d', strtotime('+'.($date - 1).' days', strtotime('1900-01-01'))).' '
                .gmdate('H:i:s', $this->column('CRE-TIME') * 60 * 60 * 24) : date('2021-12-21 17:28:00');
            $document_name = $this->column('DOC-NAME');
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
                if ($type === 'Feedback List') {
                    if (!isset($feedback_list_types[$feedback_list_type])) {
                        $fbl_type = FeedbackListType::firstOrCreate(
                            [
                                'reference' => $feedback_list_type,
                            ],
                            [
                                'name' => $this->column('PRE-TITLE FBL'),
                            ]
                        );
                        $feedback_list_types[$feedback_list_type] = $fbl_type->id;
                    }
                    $document->setMeta('feedback_list_type_id', $feedback_list_types[$feedback_list_type]);
                    $document->save();
                }
            }
            if (!isset($variant_documents[$document_id][$variant])) {
                VariantDocument::firstOrCreate(
                    [
                        'variant_id'  => $variants[$projects[$project_reference]][$variant],
                        'document_id' => $document_id,
                        'issue'       => $issue,
                    ],
                    [
                        'created_at' => $date,
                        'reference'  => $this->column('DOC-IDENTIFICATION'),
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
    private function column($key)
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
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $this->projects = json_decode(
            \Storage::get('modules/aegis/import/projects.json'),
            true
        );
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Updating data with documents',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !isset($row[1])) {
                continue;
            }

            $this->row         = $row;
            $project_reference = $this->column('PROJECT-IDENTIFICATION');
            $reference         = $this->column('DOC-IDENTIFICATION');

            if (!isset($this->projects[$project_reference])) {
                $this->errors['Documents'][$reference] = 'Project not found';
                continue;
            }

            $variant_number = $this->column('VARIANT NUMBER');

            if (!isset($this->projects[$project_reference]['variants'][$variant_number])) {
                $this->errors['Documents'][$reference] = 'Project Variant ('.$variant_number.') not found';
                continue;
            }

            $created_at = $this->column('CREATION-DATE');

            if (!$created_at) {
                $created_at = date('Y-m-d H:i:s');
            } else {
                $created_at = $this->date_convert('CREATION-DATE', 'CRE-TIME');
            }

            $author             = strtolower($this->column('AUTHOR'));
            $category           = $this->column('DOC-TYPE');
            $category_prefix    = $this->column('DOC-LETTER');
            $feedback_list      = null;
            $name               = $this->column('DOC-NAME');
            $issue              = $this->column('ISSUE');

            if (strlen($name) > 191) {
                $name = substr($name, 0, 188).'...';
            }

            if ($category_prefix === 'FBL') {
                $feedback_list_type = $this->column('FBL TYPE');
                $feedback_list      = [
                    'final' => false,
                    'name'  => $this->column('PRE-TITLE FBL'),
                    'type'  => $feedback_list_type,
                ];
            }

            $this->projects[$project_reference]['variants'][$variant_number]['documents'][$reference][$issue] = [
                'approval'        => [],
                'category'        => $category,
                'category_prefix' => $category_prefix,
                'comments'        => [],
                'created_at'      => $created_at,
                'created_by'      => $author,
                'feedback_list'   => $feedback_list,
                'name'            => $name,
                'status'          => 'Approved',
            ];
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/projects_and_documents.json', json_encode($this->projects));
    }
}
