<?php
$processes = [
    'Archived' => [
        'id'     => null,
        'stages' => [
            [
                'approvals' => 1,
                'name'      => 'Approval',
                'items'     => [
                    [
                        'groups'   => ['Approvers'],
                        'required' => false,
                    ],
                    [
                        'groups'   => ['Approvers'],
                        'required' => false,
                    ],
                ],
            ],
        ],
        'types'  => [
            'Archived',
        ],
    ],
    'Feedback List' => [
        'id'     => null,
        'stages' => [
            [
                'approvals' => 2,
                'name'      => 'Assessors',
                'items'     => [
                    [
                        'groups'   => ['Assessors'],
                        'required' => true,
                    ],
                    [
                        'groups'   => ['Assessors'],
                        'required' => false,
                    ],
                    [
                        'groups'   => ['Assessors'],
                        'required' => false,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Approvers'],
                        'required' => true,
                    ],
                ],
            ],
        ],
        'types'   => [
            'Feedback List',
        ],
    ],
    'Letter / Memo' => [
        'id'     => null,
        'stages' => [
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Approvers'],
                        'required' => true,
                    ],
                ],
            ],
        ],
        'types'  => [
            'File Note',
            'Letter',
            'Memo',
        ],
    ],
    'Proposal' => [
        'id'     => null,
        'stages' => [
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Approvers'],
                        'required' => true,
                    ],
                ],
            ],
        ],
        'types'   => [
            'Proposal',
            'Variation Order',
        ],
    ],
    'Report' => [
        'id'     => null,
        'stages' => [
            [
                'approvals' => 1,
                'name'      => 'Checker',
                'items'     => [
                    [
                        'groups'   => ['Checkers'],
                        'required' => true,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Approvers'],
                        'required' => true,
                    ],
                ],
            ],
        ],
        'types'   => [
            'Report',
        ],
    ],
];
