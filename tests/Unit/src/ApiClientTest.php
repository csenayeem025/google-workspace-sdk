<?php

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;



test('setConnectionKey() - it can set the connection_key', function(){
    $client = new ApiClientFake('test');
    $client->setConnectionKey('testing');
    expect($client->connection_key)->toBe('testing');
});

test('construct() - it can set the connection_config with json_key', function(){
    $fake_json_key = '
    {
        "type": "service_account",
        "project_id": "fake_project_id",
        "private_key_id": "123456",
        "private_key": "private_key",
        "client_email": "@dwheeler-dev-26955.iam.gserviceaccount.com",
        "client_id": "116689744120120203480",
        "auth_uri": "https://accounts.google.com/o/oauth2/auth",
        "token_uri": "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/it-ops-laravel-scripts-dwheele%40dwheeler-dev-26955.iam.gserviceaccount.com"
    }
    
    ';
    $api_client = new ApiClientFake(null, [
        'api_scopes' => [
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ],
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key' => $fake_json_key,
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ], true);
    expect($api_client->connection_config['json_key'])->toBe($fake_json_key);
});

test('construct() - it can set the connection_config with json_key_file_path', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key_file_path' => storage_path('a_file_path'),
    ], true);
    expect($client->connection_config['json_key_file_path'])->toBe(storage_path('a_file_path'));
});

test('construct() - it will set connection key if not provided', function(){
   $client = new ApiClientFake();
   expect($client->connection_key)->toBe('test');
});

test('setLogChannels() - it can set log channels from a connection key', function(){
    $api_client = new ApiClientFake('test');
    $api_client->setUp();
    expect($api_client->getLogChannels())->toBe(['single']);
});


test('setLogChannels() - it can set log channels from the connection config array', function(){
    $api_client = new ApiClientFake(null, [
        'api_scopes' => [
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ],
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $api_client->setUp();
    expect($api_client->getLogChannels())->toBe(['single']);
});


test('parseConfigFile() - it can parse the configuration file', function(){
    $api_client = new ApiClientFake('test');
    $file_contents = $api_client->parseConfigFile('test');
    expect($file_contents)->toBe([
        'api_scopes' => config('tests.connections.test.api_scopes'),
        'subject_email' => config('tests.connections.test.subject_email'),
        'json_key_file_path' => config('tests.connections.test.json_key_file_path')
    ]);
});

test('getConfigApiScopes() - it throws exception if api_scopes is null', function(){
    $api_client = new ApiClientFake('test_api_scopes_null');
    $api_client->setUp();
    $api_client->getConfigApiScopes('test_api_scopes_null');
})->expectErrorMessage('No api_scopes have been set in the configuration file you are using.');

test('getConfigApiScopes() - it throws exception if api_scopes is not set', function(){
    $api_client = new ApiClientFake('test_api_scopes_not_set');
    $api_client->setUp();
    $api_client->getConfigApiScopes('test_api_scopes_not_set');
})->expectErrorMessage('No api_scopes have been set in the configuration file you are using.');

test('getConfigApiScopes() - it will throw error if scopes are not set', function(){
    $api_client = new ApiClientFake('test');
    $api_client->setUp();
    $api_scopes = $api_client->getConfigApiScopes('test');
    expect($api_scopes)->toBe(config('tests.connections.test.api_scopes'));
});

test('getConfigSubjectEmail() - it can get the subject email from configuration file', function(){
    $api_client = new ApiClientFake('test');
    $api_client->setUp();
    $subject_email = $api_client->getConfigSubjectEmail('test');
    expect($subject_email)->toBe(config('tests.connections.test.subject_email'));
});

test('getConfigSubjectEmail() - it will set subject_email to null if not provided', function(){
    $api_client = new ApiClientFake('test_with_no_email');
    $api_client->setUp();
    $subject_email = $api_client->getConfigSubjectEmail('test_with_no_email');
    expect($subject_email)->toBe(null);
});

test('getConfigSubjectEmail() - it will set subject_email to null if parameter is missing', function(){
    $api_client = new ApiClientFake('test_with_no_email_parameter');
    $api_client->setUp();
    $subject_email = $api_client->getConfigSubjectEmail('test_with_no_email_parameter');
    expect($subject_email)->toBe(null);
});

test('getConfigJsonFilePath() - it will get the json file path from configuration file', function(){
    $api_client = new ApiClientFake('test');
    $api_client->setUp();
    $json_key_file_path = $api_client->getConfigJsonFilePath('test');
    expect($json_key_file_path)->toBe(config('tests.connections.test.json_key_file_path'));
});

test('getConfigJsonFilePath() - it will throw error if file_path is null', function(){
    $api_client = new ApiClientFake('test_with_no_json_key_file_path');
})->expectExceptionMessage('The configuration file does not contain a json_key_file_path');

test('getConfigJsonFilePath() - it will throw error if file_path set', function(){
    $api_client = new ApiClientFake('test_with_no_json_key_file_path_parameter');
})->expectExceptionMessage('The configuration file does not contain a json_key_file_path');

test('parseConnectionConfigArray() - it can parse the connection config array appropriately', function() {
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $connnection_array = [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ];

    $expected_result = [
        'api_scopes' => $api_scopes,
        'subject_email' => config('tests.connections.test.subject_email'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'json_key' => null
    ];
    $api_client = new ApiClientFake(null, $connnection_array);
    $array_contents = $api_client->parseConnectionConfigArray($connnection_array);
    expect($array_contents)->toBe($expected_result);
});

test('getConfigApiScopes() - it can get the api scopes from configuration file', function(){
    $api_client = new ApiClientFake('test');
    $api_client->setUp();
    $api_scopes = $api_client->getConfigApiScopes('test');
    expect($api_scopes)->toBe(config('tests.connections.test.api_scopes'));
});

test('getConfigArrayApiScopes() - it will get the api scopes from config array', function(){
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $api_client = new ApiClientFake(null, [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $api_scopes = $api_client->getConfigArrayJsonKey($api_client->connection_config);
    expect($api_scopes)->toBe($api_scopes);
});

test('getConfigArraySubjectEmail() - it will get the subject email from config array', function(){
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $api_client = new ApiClientFake(null, [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $subject_email = $api_client->getConfigArraySubjectEmail($api_client->connection_config);
    expect($subject_email)->toBe(config('tests.connections.test.subject_email'));
});

test('getConfigArraySubjectEmail() - it will set the subject email to null if parameter is missing', function(){
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $api_client = new ApiClientFake(null, [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
    ]);
    $subject_email = $api_client->getConfigArraySubjectEmail($api_client->connection_config);
    expect($subject_email)->toBe(null);
});

test('getConfigArrayFilePath() - it will get the file path from config array', function(){
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $api_client = new ApiClientFake(null, [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $file_path = $api_client->getConfigArrayFilePath($api_client->connection_config);
    expect($file_path)->toBe(config('tests.connections.test.json_key_file_path'));
});

test('getConfigArrayJsonKey() - it will get the json_key from config array', function(){
    $api_scopes = [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ];
    $json_key = fopen(storage_path('keys/glamstack-google-workspace/test.json'), 'r');
    $file_contents = fread($json_key,filesize(storage_path('keys/glamstack-google-workspace/test.json')));
    fclose($json_key);
    $api_client = new ApiClientFake(null, [
        'api_scopes' => $api_scopes,
        'customer_id' => config('tests.connections.test.customer_id'),
        'domain' => config('tests.connections.test.domain'),
        'json_key' => $file_contents,
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $key = $api_client->getConfigArrayJsonKey($api_client->connection_config);
    expect($key)->toBe($file_contents);
});
