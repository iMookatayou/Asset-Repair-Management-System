<?php

return [
    /**
     * Max upload size in kilobytes (default 10 MB)
     */
    'max_kb' => env('UPLOAD_MAX_KB', 10240),

    /**
     * Allowed mimetypes for attachments.
     * This is used with the `mimetypes:` validator rule.
     */
    'mimetypes' => [
        'image/*',
        'application/pdf',
    ],
];
