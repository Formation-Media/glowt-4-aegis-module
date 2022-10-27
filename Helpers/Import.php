<?php

namespace Modules\AEGIS\Helpers;

class Import
{
    public static function document_signature_store($stream, $row, &$document)
    {
        extract($row);

        if ($reviewer['author']) {
            $document['approval']['reviewer'][$document_issue][0][$reviewer['author']] = [
                'comments'            => $reviewer['comments'],
                'created_at'          => $created_at,
                'role'                => $reviewer['role'],
                'signature_reference' => '',
                'status'              => $status,
                'updated_at'          => $reviewer['date'],
            ];
        }

        if ($approver['author']) {
            $document['approval']['approver'][$document_issue][0][$approver['author']] = [
                'comments'            => $approver['comments'],
                'created_at'          => $approver['date'],
                'role'                => $approver['role'],
                'signature_reference' => '',
                'status'              => $status,
                'updated_at'          => $approver['date'],
            ];
        }
        if ($assessor_1) {
            $document['approval']['assessor'][$document_issue][0][$assessor_1] = [
                'created_at' => $created_at,
                'status'     => $status,
                'updated_at' => $created_at,
            ];
        }
        if ($assessor_2) {
            $document['approval']['assessor'][$document_issue][1][$assessor_2] = [
                'created_at' => $created_at,
                'status'     => $status,
                'updated_at' => $created_at,
            ];
        }
        if ($submitted['comments']) {
            $comments[] = [
                'author'     => $submitted['author'],
                'created_at' => $created_at,
                'content'    => $submitted['comments'],
                'updated_at' => $submitted['date'],
            ];
        }

        $document['comments']        = $comments;
        $document['issue']           = max($document['issue'], $document_issue);
        $document['statuses'][]      = $status;
        $document['submitted_at']    = $submitted['date'];
        $document['submitted_by']    = $submitted['author'];
        $document['created_by_role'] = $role;

        if ($document['category_prefix'] === 'FBL') {
            $document['feedback_list']['final'] = $fbl_final;
        }

        return $document;
    }
}
