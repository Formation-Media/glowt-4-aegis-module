<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\VariantDocument;

class DocumentSignatureImport implements ToCollection
{
    private $errors;
    private $method;
    private $projects;
    private $row;
    private $statuses;
    private $stream;
    private $users;

    public function __construct(SSEStream $stream, $users, $method)
    {
        $this->method   = $method;
        $this->statuses = [
            'APPROVED'  => 'Approved',
            'REJECTED'  => 'Denied',
            'REVIEWED'  => 'Awaiting Decision',
            'SUBMITTED' => 'Awaiting Decision',
        ];
        $this->stream = $stream;
        $this->users  = $users;
    }
    public function collection(Collection $rows)
    {
        if ($this->method === 2) {
            return $this->method_2($rows);
        }
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $temp_data         = [];
        $invalid_documents = [];
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $this->row   = $row;
            $document_id = $this->column('DOC-IDENTIFICATION');
            if (!($document = VariantDocument
                ::with([
                    'document',
                    'document.category',
                    'document.category.approval_process',
                    'document.category.approval_process.approval_process_stages',
                ])
                ->where('reference', $document_id)
                ->first()
            )) {
                $invalid_documents[] = $document_id;
                continue;
            }
            if (!$this->column('CREATION-DATE')) {
                $invalid_documents[] = $document_id;
                continue;
            }
            $comment_submitter      = str_replace('---', '', $this->column('COMMENT-SUBMITTER'));
            $comment_submitter_name = strtolower($this->column('SUBMITTER-NAME'));
            $comment_reviewer_name  = strtolower($this->column('REVIEWER'));
            $comment_approver       = str_replace('---', '', $this->column('COMMENT-APPROVER'));
            $comment_approver_name  = strtolower($this->column('APPROVER'));
            $created_at             = $this->date_convert('CREATION-DATE', 'CRE-TIME');
            $final_feedback_list    = strtolower($this->column('FBL_FINAL'));
            $is_final_feedback      = $final_feedback_list ? ($final_feedback_list === 'yes' ? true : false) : null;
            $lower_author           = strtolower($this->column('AUTHOR'));
            $old_status             = $this->column('STATUS');
            $reject_date            = $this->column('REJECT DATE');
            $rejected_at            = !$reject_date || $reject_date === '---' ? null : $this->date_convert('REJECT DATE', 'REJ-TIME');
            $review_date            = $this->column('REVIEW-DATE');
            $reviewed_at            = !$review_date || $review_date === '---' ? null : $this->date_convert('REVIEW-DATE', 'REV-TIME');
            $submitted_at           = $this->date_convert('SUBMIT-DATE', 'SUB-TIME');
            $updated_at             = date('Y-m-d H:i:s', max(
                strtotime($created_at),
                strtotime($rejected_at),
                strtotime($reviewed_at),
                strtotime($submitted_at),
            ));
            if ($is_final_feedback !== null) {
                $document->document->setMeta('final_feedback_list', $is_final_feedback);
                $document->document->save();
            }
            if (!isset($this->users[$lower_author]['id'])) {
                $first_name   = ucwords(substr($lower_author, 0, 1));
                $last_name    = ucwords(substr($lower_author, 1));
                $user_details = [
                    'email'      => $this->users[$lower_author]['email'],
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
                $this->users[$lower_author]['id'] = $user->id;
            }
            $user_id = $this->users[$lower_author]['id'];
            $temp_data[$document->document_id] = [
                'agent_id'         => $user_id,
                'approval_item_id' => null,         // Handled by Signature Import
                'comments'         => array_filter([
                    $comment_submitter ? [
                        'content' => $comment_submitter,
                        'user_id' => $this->users[$comment_submitter_name]['id'] ?? $comment_submitter_name,
                    ] : null,
                    $comment_reviewer ? [
                        'content' => $comment_reviewer,
                        'user_id' => $this->users[$comment_reviewer_name]['id'] ?? $comment_reviewer_name,
                    ] : null,
                    $comment_approver ? [
                        'content' => $comment_approver,
                        'user_id' => $this->users[$comment_approver_name]['id'] ?? $comment_approver_name,
                    ] : null,
                ]),
                'created_at'  => $created_at,
                'document_id' => $document->document_id,
                'reference'   => null,         // Handled by Signature Import
                'status'      => $this->statuses[$old_status],
                'updated_at'  => $updated_at,
            ];
            // Error on null of anything but reference and approval_item_id
            foreach ($temp_data[$document->document_id] as $key => $value) {
                if (!in_array($key, ['reference', 'approval_item_id', 'comments']) && !$value) {
                    \Log::debug([
                        __FILE__ => __LINE__,
                        'Attribute "'.$key.'" is not set.',
                    ]);
                }
            }
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
        file_put_contents(\Module::getModulePath('AEGIS').'/Resources/files/import/temp_data.json', json_encode($temp_data));
        $this->stream->send([
            'percentage' => 100,
            'message'    => count($invalid_documents)
                ? '&nbsp;&nbsp;&nbsp;<span style="color:red;">Could not import signature for documents:</span>'
                    .'<ol><li><span style="color:red;">'.implode('</span></li><li><span style="color:red;">', $invalid_documents)
                    .'</span></li></ol>'
                : '',
        ]);
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
    private function method_2($rows)
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
            $document['issues'][]     = $issue;
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
}
