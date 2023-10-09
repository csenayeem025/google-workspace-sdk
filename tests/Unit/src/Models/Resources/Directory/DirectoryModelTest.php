<?php

namespace Glamstack\GoogleWorkspace\Tests\Unit\src\Models\Resources\Directory;


use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\Directory\Directory;
use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('verifyConfigArray() - it requires api_scopes to be set', function(){
    $api_client = new ApiClientFake(null,[
        'customer_id' => 'fake_id',
        'domain' => 'fake_domain',
        'json_key_file_path' => storage_path('a_file_path')
    ]);

    $directory_client = new Directory($api_client);
})->expectExceptionMessage('The api scopes field is required.');

test('verifyConfigArray() - it requires customer_id to be set', function(){
    $api_client = new ApiClientFake(null, [
        'api_scopes' => [
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ],
        'domain' => config('tests.connections.test.domain'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $directory_client = new Directory($api_client);
})->expectExceptionMessage('The customer id field is required.');

test('verifyConfigArray() - it requires domain to be set', function(){
    $api_client = new ApiClientFake(null, [
        'api_scopes' => [
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ],
        'customer_id' => config('tests.connections.test.customer_id'),
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $directory_client = new Directory($api_client);
})->expectExceptionMessage('The domain field is required.');


test('verifyConfigArray() - it will set the config array properly', function(){
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

    $directory_client = new Directory($api_client);
    expect($directory_client->connection_config['domain'])->toBe(config('tests.connections.test.domain'))
        ->and($directory_client->connection_config['api_scopes'])->toBe([
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ])
        ->and($directory_client->connection_config['customer_id'])->toBe(config('tests.connections.test.customer_id'))
        ->and($directory_client->connection_config['json_key_file_path'])->toBe(storage_path('keys/glamstack-google-workspace/test.json'));
});
