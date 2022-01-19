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
                        'groups'   => ['Archived'],
                        'required' => false,
                    ],
                    [
                        'groups'   => ['Archived'],
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
                'approvals' => 1,
                'name'      => 'Author',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
                        'required' => true,
                    ],
                ],
            ],
            [
                'approvals' => 2,
                'name'      => 'Assessors',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
                        'required' => true,
                    ],
                    [
                        'groups'   => ['Archived'],
                        'required' => false,
                    ],
                    [
                        'groups'   => ['Archived'],
                        'required' => false,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
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
                        'groups'   => ['Archived'],
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
                'name'      => 'Author',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
                        'required' => true,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
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
                'name'      => 'Author',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
                        'required' => true,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Checker',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
                        'required' => true,
                    ],
                ],
            ],
            [
                'approvals' => 1,
                'name'      => 'Approver',
                'items'     => [
                    [
                        'groups'   => ['Archived'],
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
