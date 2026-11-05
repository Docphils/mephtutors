<?php

return [
    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:51200'],
        'max_upload_time' => 10,
    ],
];
