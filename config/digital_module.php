<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Digital Module Enabled
    |--------------------------------------------------------------------------
    |
    | This value determines if the digital document add-on features are active.
    | Set to false to hide upload UI and routing attachments while retaining data.
    |
    */
    'enabled' => env('DIGITAL_MODULE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Upload Constraints
    |--------------------------------------------------------------------------
    |
    | Define the maximum allowed file size (in KB) and the list of permitted
    | MIME types to ensure system security and storage efficiency.
    |
    */
    'max_upload_size_kb' => env('DIGITAL_MODULE_MAX_UPLOAD_SIZE', 10240), // 10MB default

    'allowed_mimes' => [
        'pdf', 'jpeg', 'png', 'jpg', 'doc', 'docx', 'xls', 'xlsx'
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Define which filesystem disk to use. By default and strict recommendation,
    | this should be a private 'local' disk not exposed to the public web root.
    |
    */
    'disk' => env('DIGITAL_MODULE_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Retention Policy Configuration
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'routine' => [
            'age_days' => 1825, // 5 years
            'action' => 'soft_delete',
            'types' => ['Memo', 'Official Letter', 'Draft'],
        ],
        'financial' => [
            'age_days' => 3650, // 10 years
            'action' => 'archive_to_cold_storage', // Just an example constant for future expansion
            'department_codes' => ['FIN', 'AUD'],
        ],
        'critical' => [
            'action' => 'none',
            'clearance_levels' => [4, 5],
        ],
    ],
];
