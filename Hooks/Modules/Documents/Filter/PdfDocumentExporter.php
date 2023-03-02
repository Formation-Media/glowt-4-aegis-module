<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Filter;

use App\Helpers\Dates;
use Modules\AEGIS\Models\VariantDocument;

class PdfDocumentExporter
{
    public static function run(&$pdf)
    {
        $pdf = function ($pdf) {
            $pdf->h2('dictionary.results');
            $pdf->hr();
            $pdf->ln(2);
            if ($pdf->exporter->documents) {
                foreach ($pdf->exporter->documents as $i => $document) {
                    if ($i !== 0) {
                        $pdf->addPage();
                    }

                    $variant_document = VariantDocument
                        ::with([
                            'document',
                            'project',
                            'project.company',
                        ])
                        ->firstWhere('document_id', $document->id);

                    $document = $variant_document->document;

                    $pdf->h3($document->name);
                    $details = [
                        'dictionary.reference' => $variant_document?->reference,
                        'dictionary.project'   => $variant_document?->project->reference,
                        'dictionary.company'   => $variant_document?->project->company->name,
                        'dictionary.process'   => $document->approval_process?->name,
                        'dictionary.status'    => $document->status,
                        'dictionary.added'     => Dates::datetime($document->created_at),
                        'phrases.added-by'     => $document->created_by->name,
                        'dictionary.updated'   => Dates::datetime($document->updated_at),
                        'phrases.updated-by'   => $document->updated_by->name,
                    ];
                    $pdf->columns($details);
                    if ($document->description) {
                        $pdf->p($document->description);
                    }

                    $approval_process = $document->document_approval_process_items();
                    if ($approval_process->count()) {
                        $data = [];
                        foreach ($approval_process->get() as $item) {
                            if ($stage = $item->approval_process_item?->approval_stage) {
                                $stage_number = $stage->number.' of '.$stage->approval_process?->approval_process_stages->count();
                                if ($stage_number) {
                                    if ($item->status === 'Approved') {
                                        $status = ___('dictionary.approved');
                                    } elseif ($item->status === 'Rejected') {
                                        $status = ___('dictionary.rejected');
                                    } elseif ($item->status === 'Awaiting Decision') {
                                        $status = ___('documents::phrases.awaiting-decision');
                                    }

                                    $data[]       = [
                                        $stage->name.' ('.$stage_number.')',
                                        $item->agent?->name,
                                        $status,
                                        Dates::datetime($item->updated_at),
                                    ];
                                }
                            }
                        }
                        if ($data) {
                            $pdf->h4('documents::phrases.approval-process');
                            $pdf->table(
                                $data,
                                array(
                                    'dictionary.name',
                                    'dictionary.approver',
                                    'dictionary.status',
                                    'dictionary.updated',
                                ),
                            );
                        }
                    }
                    $comments = $document->comments;
                    if ($comments->count()) {
                        $pdf->h4('dictionary.comments');

                        foreach ($comments as $comment) {
                            $pdf->html($comment->content);
                            $pdf->p(
                                $comment->user->name.' '.strtolower(___('dictionary.at')).' '.$comment->nice_datetime('created_at'),
                                'L'
                            );
                        }
                    }
                    $activities = $document->activity;
                    if ($activities->count()) {
                        $pdf->h4('dictionary.log');

                        $data = [];
                        foreach ($activities as $activity) {
                            $data[] = [
                                $activity->nice_datetime('created_at'),
                                ___($activity->description, $activity->properties->toArray()),
                                $activity->causer->name,
                            ];
                        }
                        $pdf->table(
                            $data,
                            array(
                                'dictionary.date',
                                'dictionary.message',
                                'dictionary.user',
                            ),
                        );
                    }
                }
            }











            // $max_activities         = 0;
            // $max_approval_processes = 0;
            // $max_comments           = 0;
            // if ($csv->exporter->documents_count) {
            //     $data = [];

            //     foreach ($csv->exporter->documents_query->get() as $document) {
            //         $variant_document = VariantDocument
            //             ::with([
            //                 'project',
            //                 'project.company',
            //             ])
            //             ->firstWhere('document_id', $document->id);

            //         $document_data = [
            //             'activities'       => [],
            //             'approval_process' => [],
            //             'comments'         => [],
            //             'details'          => [
            //                 'dictionary.project'     => $variant_document?->project->reference,
            //                 'dictionary.company'     => $variant_document?->project->company->name,
            //                 'dictionary.name'        => $document->name,
            //                 'dictionary.reference'   => $variant_document?->reference,
            //                 'dictionary.description' => $document->description ?? '',
            //                 'dictionary.process'     => $document->approval_process?->name,
            //                 'dictionary.status'      => $document->status,
            //                 'dictionary.added'       => Dates::datetime($document->created_at),
            //                 'phrases.added-by'       => $document->created_by->name,
            //                 'dictionary.updated'     => Dates::datetime($document->updated_at),
            //                 'phrases.updated-by'     => $document->updated_by->name,
            //             ],
            //         ];

            //         $approval_process = $document->document_approval_process_items();
            //         if ($approval_process->count()) {
            //             foreach ($approval_process->get() as $item) {
            //                 if ($stage = $item->approval_process_item?->approval_stage) {
            //                     $stage_number = $stage->number.' of '.$stage->approval_process?->approval_process_stages->count();
            //                     if ($stage->approval_process?->approval_process_stages->count()) {
            //                         if ($item->status === 'Approved') {
            //                             $status = ___('dictionary.approved');
            //                         } elseif ($item->status === 'Rejected') {
            //                             $status = ___('dictionary.rejected');
            //                         } elseif ($item->status === 'Awaiting Decision') {
            //                             $status = ___('documents::phrases.awaiting-decision');
            //                         }
            //                         $document_data['approval_process'][] = [
            //                             'date'     => Dates::datetime($item->updated_at),
            //                             'approver' => $item->agent?->name,
            //                             'stage'    => $stage->name.' ('.$stage_number.')',
            //                             'status'   => $status,
            //                         ];
            //                     }
            //                 }
            //             }
            //         }

            //         $comments = $document->comments;
            //         if ($comments->count()) {
            //             foreach ($comments as $comment) {
            //                 $document_data['comments'][] = [
            //                     'author'  => $comment->user->name,
            //                     'content' => strip_tags($comment->content),
            //                     'date'    => $comment->nice_datetime('created_at'),
            //                 ];
            //             }
            //         }

            //         $activities = $document->activity;
            //         if ($activities->count()) {
            //             foreach ($activities as $activity) {
            //                 $document_data['activities'][] = [
            //                     'user'    => $activity->causer->name,
            //                     'message' => ___($activity->description, $activity->properties->toArray()),
            //                     'date'    => $activity->nice_datetime('created_at'),
            //                 ];
            //             }
            //         }

            //         $max_activities         = max($max_activities, count($document_data['activities']));
            //         $max_approval_processes = max($max_approval_processes, count($document_data['approval_process']));
            //         $max_comments           = max($max_comments, count($document_data['comments']));

            //         $data[] = $document_data;
            //     }

            //     $headers = array_keys($data[0]['details']);
            //     for ($i = 0; $i < $max_approval_processes; $i++) {
            //         $headers[] = ['documents::phrases.process-item-#-stage',    ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.process-item-#-approver', ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.process-item-#-status',   ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.process-item-#-date',     ['#' => number_format($i + 1)]];
            //     }
            //     for ($i = 0; $i < $max_comments; $i++) {
            //         $headers[] = ['documents::phrases.comment-#-content', ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.comment-#-author',  ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.comment-#-date',    ['#' => number_format($i + 1)]];
            //     }
            //     for ($i = 0; $i < $max_activities; $i++) {
            //         $headers[] = ['documents::phrases.log-#-date',    ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.log-#-message', ['#' => number_format($i + 1)]];
            //         $headers[] = ['documents::phrases.log-#-user',    ['#' => number_format($i + 1)]];
            //     }
            //     $csv->header($headers);
            //     foreach ($data as $d) {
            //         $row       = $d['details'];
            //         $row_count = count($row);
            //         if ($d['approval_process']) {
            //             foreach ($d['approval_process'] as $i => $process) {
            //                 $row[] = $process['stage'];
            //                 $row[] = $process['approver'];
            //                 $row[] = $process['status'];
            //                 $row[] = $process['date'];
            //             }
            //         }
            //         $row       = array_pad($row, $row_count + ($max_approval_processes * 4), '');
            //         $row_count = count($row);
            //         if ($d['comments']) {
            //             foreach ($d['comments'] as $comment) {
            //                 $row[] = $comment['content'];
            //                 $row[] = $comment['author'];
            //                 $row[] = $comment['date'];
            //             }
            //         }
            //         $row       = array_pad($row, $row_count + ($max_comments * 3), '');
            //         if ($d['activities']) {
            //             foreach ($d['activities'] as $activity) {
            //                 $row[] = $activity['message'];
            //                 $row[] = $activity['date'];
            //                 $row[] = $activity['user'];
            //             }
            //         }
            //         $csv->row($row);
            //     }
            // }
        };
    }
}
