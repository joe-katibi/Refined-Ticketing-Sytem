<?php

return [
    'name' => 'Outages',
    'description' => 'Outages Management Module for handling network outages, tickets, and SLA tracking',
    'version' => '1.0.0',
    
    // SLA Configuration
    'sla' => [
        'default_resolution_time' => [
            'Critical' => 60, // minutes
            'High' => 120,
            'Medium' => 240,
            'Low' => 480,
        ],
        'breach_notification' => true,
        'escalation_levels' => [
            'Level 1' => 30, // minutes before escalation
            'Level 2' => 60,
            'Level 3' => 120,
        ],
    ],
    
    // Ticket Configuration
    'tickets' => [
        'auto_numbering' => true,
        'number_prefix' => 'OUT',
        'number_format' => 'Ymd', // Date format for ticket numbers
        'statuses' => [
            'Reported',
            'In Progress',
            'Resolved',
            'Closed',
        ],
        'priorities' => [
            'Low',
            'Medium',
            'High',
            'Critical',
        ],
    ],
    
    // Notification Configuration
    'notifications' => [
        'enabled' => true,
        'channels' => ['mail', 'database'],
        'sla_breach_notification' => true,
        'status_change_notification' => true,
        'assignment_notification' => true,
    ],
    
    // File Upload Configuration
    'attachments' => [
        'max_file_size' => 10240, // KB
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 
            'jpg', 'jpeg', 'png', 'gif', 
            'txt', 'log', 'zip', 'rar'
        ],
        'storage_path' => 'outages/attachments',
    ],
    
    // Dashboard Configuration
    'dashboard' => [
        'default_date_range' => 30, // days
        'refresh_interval' => 300, // seconds
        'charts_enabled' => true,
    ],
    
    // Reporting Configuration
    'reports' => [
        'export_formats' => ['csv', 'excel', 'pdf'],
        'default_format' => 'csv',
        'cache_duration' => 3600, // seconds
    ],
];
