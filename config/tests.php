<?php

return [

    /**
     * ------------------------------------------------------------------------
     * Default Configuration
     * ------------------------------------------------------------------------
     *
     * @see https://laravel.com/docs/9.x/logging
     *
     * @param string $connection
     *      The default connection key (array key) that you want to use if not
     *      specified when instantiating the ApiClient. You can add the
     *      `GOOGLE_WORKSPACE_DEFAULT_CONNECTION` variable in your .env file so you
     *      don't need to pass the connection key into the ApiClient.
     *      ```php
     *      $google_cloud = new \Glamstack\GoogleCloud\ApiClient();
     *      ```
     *
     * @param array  $log_channels
     *      The Laravel log channels to send all info and error logs to for SDK
     *      connections and authentication calls. If you leave this at the value
     *      of `['single']`, all API call logs will be sent to the default log
     *      file for Laravel that you have configured in config/logging.php
     *      which is usually storage/logs/laravel.log.
     *
     *      If you would like to see Google API logs in a separate log file
     *      that is easier to triage without unrelated log messages, you can
     *      create a custom log channel and add the channel name to the
     *      array. For example, we recommend creating a custom channel
     *      (ex. `glamstack-google-workspace`), however you can choose any
     *      name you would like.
     *      Ex. ['single', 'glamstack-google-workspace']
     *
     *      You can also add additional channels that logs should be sent to.
     *      Ex. ['single', 'glamstack-google-workspace', 'slack']
     */
    'default' => [
        'connection' => env('GOOGLE_WORKSPACE_DEFAULT_CONNECTION', 'test'),
        'log_channels' => ['single'],
    ],

    /**
     * ------------------------------------------------------------------------
     * Service Account Connections Configuration
     * ------------------------------------------------------------------------
     *
     * Each service account is considered a "connection" and has an array key
     * that we refer to as the "connection key" that contains a array of
     * configuration values and is used when the ApiClient is instantiated.
     *
     * If you're just getting started, you can use the `test` connection
     * key and create a service account with the following API Scopes:
     *  [
     *      'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/admin.directory.user'
     *  ]
     *
     * ```php
     * $google_auth = new \Glamstack\GoogleWorkspace\ApiClient('test');
     * ```
     *
     * You can create one or more service accounts in your GCP project(s) with
     * different roles and permissions based on your least privilege model. You
     * can add additional connection keys for each of your service accounts
     * using a snake case name of your choosing.
     *
     * @param array $api_scopes
     *      The API OAUTH scopes that will be needed for the Google Workspace API endpoints
     *      that will be used. These need to match what you have granted your
     *      service account.
     *
     *      https://developers.google.com/identity/protocols/oauth2/scopes
     *
     *      ```php
     *      [
     *          'https://www.googleapis.com/auth/admin.directory.group',
     *          'https://www.googleapis.com/auth/admin.directory.user'
     *      ]
     *      ```
     *
     * @param string $json_key_file_path
     *      You can specify the full operating system path to the JSON key file.
     *
     *      If null, the GCP service account JSON API key file that you
     *      generate and download should be added to your locally cloned
     *      repository in the `storage/keys/glamstack-google-workspace` directory with
     *      the filename that matches the connection key.
     *
     *      ```php
     *      storage('keys/glamstack-google-workspace/test.json')
     *      ```
     *
     * @param string $customer_id
     *      The customer number of the Google Account that the API's will be
     *      run on. This will need to match the customer number that the 
     *      Service Account is under as well or it will not work.
     *
     * @param string $domain
     *      The domain of the Google Account that the API's will be run on. This
     *      will need to match the domain that the Service Account is created under.
     *
     * @param ?string $subject_email
     *      The email of the address to run the Google Workspace API as. If
     *      this is not set then it will use the client_email from the JSON Key.
     *
     * @param array  $log_channels
     *      The Laravel log channels to send all related info and error
     *      logs to. If you leave this at the value of `['single']`, all
     *      API call logs will be sent to the default log file for Laravel
     *      that you have configured in `config/logging.php` which logs to
     *      `storage/logs/laravel.log`.
     *
     *      If you would like to see Google API logs in a separate log file
     *      that is easier to triage without unrelated log messages, you can
     *      create a custom log channel and add the channel name to the
     *      array. For example, we recommend creating a custom channel
     *      (ex. `glamstack-google-workspace`), however you can choose any
     *      name you would like. You could also use a log file name that
     *      is the name of the GCP project that the logs relate to.
     *      ```php
     *      ['single', 'glamstack-google-workspace']
     *      ```
     *
     *      You can also add additional channels that logs should be sent to.
     *      ```php
     *      ['single', 'glamstack-google-workspace', 'slack']
     *      ```
     */
    'connections' => [

        'test' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/admin.directory.user',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/drive',
                'https://mail.google.com/',
                'https://www.googleapis.com/auth/apps.licensing',
                'https://www.googleapis.com/auth/gmail.settings.sharing',
                'https://www.googleapis.com/auth/gmail.settings.basic',
                'https://www.googleapis.com/auth/ediscovery'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL'),
            'test_group_email' => env('GOOGLE_WORKSPACE_TEST_GROUP_EMAIL'),
            'test_sheet_id' => env('GOOGLE_WORKSPACE_TEST_SHEET_ID')
        ],

        'test_with_incorrect_permissions' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.userss'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_customer_id_null' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => null,
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_customer_id_not_set' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => null,
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_email_is_null' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_api_scopes_null' => [
            'api_scopes' => null,
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_api_scopes_not_set' => [
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_with_no_json_key_file_path' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => null,
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_with_no_json_key_file_path_parameter' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL')
        ],

        'test_with_no_email' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => null
        ],

        'test_with_no_email_parameter' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/contacts'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'project_id' => 'testing',
        ],
    ]
];
