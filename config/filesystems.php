<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'member_avatars' => [
            'driver' => 'local',
            'root' => public_path('upload/member'),
            'url' => env('APP_URL').'/upload/member',
            'visibility' => 'public',
        ],

        'club_images' => [
            'driver' => 'local',
            'root' => storage_path('app/public/upload/club'),
            'url' => env('APP_URL') . '/storage/upload/club',
            'visibility' => 'public',
        ],

       'club_register_tracker' => [
            'driver' => 'local',
            'root' => storage_path('app/public/upload/club_register_tracker'),
            'url' => env('APP_URL').'/storage/upload/club_register_tracker',
            'visibility' => 'public',
        ],
        'exam-registration' => [
            'driver' => 'local',
            'root' => storage_path('app/public/upload/exam-registration'),
            'url' => env('APP_URL') . '/public/upload/exam-registration',
            'visibility' => 'public',
        ],
        'firebase' => [
            'driver' => 'firebase',
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => env('FIREBASE_CREDENTIALS'), 
            'bucket' => env('FIREBASE_STORAGE_BUCKET'),
            'cache' => [
                'store' => 'memcached', 
            ]
        ],

        
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
    
];
