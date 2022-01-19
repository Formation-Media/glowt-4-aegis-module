<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\VariantDocument;

class DocumentSignatureImport implements ToCollection
{
    private $row;
    private $stream;
    private $users;

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
        $temp_data         = [];
        $invalid_documents = [];
        $new_statuses      = [
            'APPROVED'  => 'Approved',
            'REJECTED'  => 'Denied',
            'REVIEWED'  => 'Denied',
            'SUBMITTED' => 'Awaiting Decision',
        ];
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
            $comment_reviewer       = str_replace('---', '', $this->column('COMMENT-REVIEWER'));
            $comment_reviewer_name  = strtolower($this->column('REVIEWER'));
            $comment_approver       = str_replace('---', '', $this->column('COMMENT-APPROVER'));
            $comment_approver_name  = strtolower($this->column('APPROVER'));
            $created_at        = $this->date_convert('CREATION-DATE', 'CRE-TIME');
            $lower_author      = strtolower($this->column('AUTHOR'));
            $old_status        = $this->column('STATUS');
            $reject_date       = $this->column('REJECT DATE');
            $rejected_at       = !$reject_date || $reject_date === '---' ? null : $this->date_convert('REJECT DATE', 'REJ-TIME');
            $review_date       = $this->column('REVIEW-DATE');
            $reviewed_at       = !$review_date || $review_date === '---' ? null : $this->date_convert('REVIEW-DATE', 'REV-TIME');
            $submitted_at      = $this->date_convert('SUBMIT-DATE', 'SUB-TIME');
            $updated_at        = date('Y-m-d H:i:s', max(
                strtotime($created_at),
                strtotime($rejected_at),
                strtotime($reviewed_at),
                strtotime($submitted_at),
            ));
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
                'status'      => $new_statuses[$old_status],
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
}
