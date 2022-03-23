<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DocumentsImport implements ToCollection
{
    private $errors;
    private $projects;
    private $row;
    private $stream;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
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

            $this->row = $row;

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

            $this->projects[$project_reference]['variants'][$variant_number]['documents'][$reference] = [
                'approval'        => [],
                'category'        => $category,
                'category_prefix' => $category_prefix,
                'comments'        => [],
                'created_at'      => $created_at,
                'created_by'      => $author,
                'feedback_list'   => $feedback_list,
                'issue'           => $this->column('ISSUE'),
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
}
