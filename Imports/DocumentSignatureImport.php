<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DocumentSignatureImport implements ToCollection
{
    private $errors;
    private $projects;
    private $row;
    private $statuses;
    private $stream;

    public function __construct(SSEStream $stream,)
    {
        $this->statuses = [
            'APPROVED'  => 'Approved',
            'REJECTED'  => 'Rejected',
            'REVIEWED'  => 'Awaiting Decision',
            'SUBMITTED' => 'Awaiting Decision',
        ];
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $this->errors = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        $this->projects = json_decode(
            \Storage::get('modules/aegis/import/projects_and_documents.json'),
            true
        );
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Updating data with document signatures',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !isset($row[1])) {
                continue;
            }

            $this->row = $row;

            $document_reference = $this->column('DOC-IDENTIFICATION');
            $project_reference  = $this->column('PROJECT IDENTIFICATION');

            if (!isset($this->projects[$project_reference])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Not Found';
                continue;
            }

            $project_variant = $this->column('VARIANT NUMBER');

            if (!isset($this->projects[$project_reference]['variants'][$project_variant])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Variant ('.$project_variant.') not found';
                continue;
            }
            if (!isset($this->projects[$project_reference]['variants'][$project_variant]['documents'])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Variant ('.$project_variant.') has no documents';
                continue;
            }
            if (!isset($this->projects[$project_reference]['variants'][$project_variant]['documents'][$document_reference])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project Variant ('.$project_variant
                    .') does not have a document with reference '.$document_reference;
                continue;
            }

            $created_at   = $this->column('CREATION-DATE');
            $issue        = $this->column('ISSUE');
            $submitted_at = $this->column('SUBMIT-DATE');

            $approval_date       = $this->column('APPROVAL-DATE');
            $assessor_1          = $this->column('ASSESSOR_1');
            $assessor_2          = $this->column('ASSESSOR_2');
            $approver_comments   = str_replace('---', '', $this->column('COMMENT-APPROVER'));
            $approver_reference  = strtolower(str_replace('---', '', $this->column('APPROVER')));
            $approver_role       = str_replace('---', '', $this->column('APPROVER-ROLE'));
            $author_role         = str_replace('---', '', $this->column('AUTHOR-ROLE'));
            $comments            = [];
            $created_at          = $created_at !== null ? $this->date_convert('CREATION-DATE', 'CRE-TIME') : date('Y-m-d H:i:s');
            $document            = $this->projects[$project_reference]['variants'][$project_variant]['documents'][$document_reference];
            $review_date         = $this->column('REVIEW-DATE');
            $reviewer_comments   = str_replace('---', '', $this->column('COMMENT-REVIEWER'));
            $reviewer_reference  = strtolower(str_replace('---', '', $this->column('REVIEWER')));
            $reviewer_role       = str_replace('---', '', $this->column('REVIEWER-ROLE'));
            $status              = $this->statuses[$this->column('STATUS')];
            $submitted_at        = $submitted_at !== null ? $this->date_convert('SUBMIT-DATE', 'SUB-TIME') : date('Y-m-d H:i:s');
            $submitter_comments  = str_replace('---', '', $this->column('COMMENT-SUBMITTER'));
            $submitter_reference = strtolower($this->column('SUBMITTER-NAME'));

            $approved_at = !$approval_date || $approval_date === '---' ? null : $this->date_convert('APPROVAL-DATE', 'APP-TIME');
            $reviewed_at = !$review_date || $review_date === '---' ? null : $this->date_convert('REVIEW-DATE', 'REV-TIME');

            if ($reviewer_reference) {
                $document['approval']['reviewer'][$issue][0][$reviewer_reference] = [
                    'comments'            => $reviewer_comments,
                    'created_at'          => $created_at,
                    'role'                => $reviewer_role,
                    'signature_reference' => '',
                    'status'              => $status,
                    'updated_at'          => $reviewed_at,
                ];
            }
            if ($approver_reference) {
                $document['approval']['approver'][$issue][0][$approver_reference] = [
                    'comments'            => $approver_comments,
                    'created_at'          => $created_at,
                    'role'                => $approver_role,
                    'signature_reference' => '',
                    'status'              => $status,
                    'updated_at'          => $approved_at,
                ];
            }
            if ($assessor_1) {
                $document['approval']['assessor'][$issue][0][$assessor_1] = [
                    'created_at' => $created_at,
                    'status'     => $status,
                    'updated_at' => $created_at,
                ];
            }
            if ($assessor_2) {
                $document['approval']['assessor'][$issue][1][$assessor_2] = [
                    'created_at' => $created_at,
                    'status'     => $status,
                    'updated_at' => $created_at,
                ];
            }

            // Comments
            if ($submitter_comments) {
                $comments[] = [
                    'author'     => $submitter_reference,
                    'created_at' => $created_at,
                    'content'    => $submitter_comments,
                    'updated_at' => $created_at,
                ];
            }

            $document['comments']     = $comments;
            $document['issue']        = max($document['issue'], $issue);
            $document['submitted_at'] = $submitted_at;
            $document['submitted_by'] = $submitter_reference;

            if ($author_role) {
                $document['author_role'] = $author_role;
            }

            if ($document['category_prefix'] === 'FBL') {
                $document['feedback_list']['final'] = strtolower($this->column('FBL_FINAL')) === 'yes' ? true : false;
            }

            $this->projects[$project_reference]['variants'][$project_variant]['documents'][$document_reference] = $document;

            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        \Storage::put('modules/aegis/import/errors.json', json_encode($this->errors));
        \Storage::put('modules/aegis/import/projects_and_document_signatures.json', json_encode($this->projects));
    }
    private function column($key)
    {
        $keys = [
            'DOC-IDENTIFICATION',
            'ISSUE',
            'DOC-NAME',
            'DOC-TYPE',
            'CREATION-DATE',
            'AUTHOR',
            'DOC-DESCRIPTION',
            'PROJECT IDENTIFICATION',
            'VARIANT NUMBER',
            'PROJECT NAME',
            'STATUS',
            'SUBMIT-DATE',
            'REVIEWER',
            'REVIEW-DATE',
            'APPROVER',
            'APPROVAL-DATE',
            'REJECT DATE',
            'AUTHOR-ROLE',
            'REVIEWER-ROLE',
            'APPROVER-ROLE',
            'COMMENT-REVIEWER',
            'SUBMITTER-NAME',
            'COMMENT-APPROVER',
            'REJECTED-BY',
            'CRE-TIME',
            'SUB-TIME',
            'REV-TIME',
            'REJ-TIME',
            'APP-TIME',
            'DOC-LETTER',
            'FBL_FINAL',
            'ASSESSOR_1',
            'ASSESSOR_2',
            'ADD_FBL_ASSESSOR',
            'CLOSED',
            'COMMENT-SUBMITTER',
            'PROPOSAL-VALUE',
            'SUBMITTED_PROPOSAL_VALUE',
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
