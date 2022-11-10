<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use Modules\AEGIS\Helpers\Import;

class RetryDocumentSignatureImport
{
    public function __construct(SSEStream $stream)
    {
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Loading data',
        ]);
        $errors = json_decode(
            \Storage::get('modules/aegis/import/errors.json'),
            true
        );
        $projects = json_decode(
            \Storage::get('modules/aegis/import/projects_and_document_signatures.json'),
            true
        );
        $rows = json_decode(
            \Storage::get('modules/aegis/import/retry_document_signatures.json'),
            true
        );
        $stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retrying failed data document signatures',
        ]);
        foreach ($rows as $i => $row) {
            extract($row);
            if (!isset($projects[$project_reference])) {
                $this->errors['Document Signatures'][$document_reference] = 'Project '.$project_reference.' not Found (L'.__LINE__.')';
                \Debug::debug('Project '.$project_reference.' not Found');
                $stream->stop();
            }
            if (!isset($projects[$project_reference]['phases'][$phase_number])) {
                \Debug::debug($document_reference, $phase_number, $project_reference);
                $stream->stop();
            }
            if (!isset($projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference])) {
                \Debug::debug($document_reference, $phase_number, $project_reference, $row);
                $stream->stop();
            }
            $document = $this->projects[$project_reference]['phases'][$phase_number]['documents'][$document_reference];
            Import::document_signature_store($stream, $row, $document);
            \Debug::debug($document);
            $stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 2),
            ]);
        }
        // $stream->stop();
        \Storage::put('modules/aegis/import/errors.json', json_encode($errors, JSON_PRETTY_PRINT));
        \Storage::put('modules/aegis/import/projects_and_document_signatures.json', json_encode($projects, JSON_PRETTY_PRINT));
    }
}
