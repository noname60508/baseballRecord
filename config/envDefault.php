<?php

return [
    'userIconPath' => __DIR__ . '/../../baseballRecordFile/user/icon',
    'url' => env('APP_URL', 'http://172.16.80.42'),
    'frontendUrl' => env('FRONTEND_URL', 'http://127.0.0.1:5173'),
    'filePath' => __DIR__ . '/../../baseballRecordFile',
    'fileUrl' => env('APP_URL', 'http://172.16.80.42') . '/baseballRecordFile',
    // token的有效期限
    'tokenExpire' => match (env('APP_DEBUG', false)) {
        false => 60 * 60 * 24, // 1 day
        default => 60 * 60 * 24 * 30, // 1 month
    },
];
