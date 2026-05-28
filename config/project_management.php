<?php

return [
    'project_lifecycle' => [
        'lead_converted',
        'project_created',
        'requirement_gathering',
        'scope_approval',
        'planning',
        'milestones',
        'task_creation',
        'assignment',
        'development',
        'qa',
        'deployment',
        'client_approval',
        'closure',
        'maintenance',
    ],

    'task_workflow_statuses' => [
        'backlog',
        'todo',
        'in_progress',
        'blocked',
        'review',
        'testing',
        'deployed',
        'completed',
        'archived',
    ],

    'task_workflow_transitions' => [
        'backlog' => ['todo', 'archived'],
        'todo' => ['in_progress', 'blocked', 'archived'],
        'in_progress' => ['blocked', 'review', 'todo'],
        'blocked' => ['todo', 'in_progress', 'archived'],
        'review' => ['testing', 'in_progress', 'blocked'],
        'testing' => ['deployed', 'in_progress', 'blocked'],
        'deployed' => ['completed', 'in_progress'],
        'completed' => ['archived'],
        'archived' => [],
    ],

    'task_dependency_types' => ['blocks', 'depends_on', 'related_to'],

    'notifications' => [
        'channels' => [
            'database' => true,
            'email' => true,
            'whatsapp_ready' => true,
        ],
        'milestone_deadline_days' => 2,
    ],
];
