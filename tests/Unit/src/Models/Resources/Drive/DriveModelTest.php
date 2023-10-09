<?php

namespace Glamstack\GoogleWorkspace\Tests\Unit\src\Models\Resources\Drive;


use Glamstack\GoogleWorkspace\Resources\Drive\Drive;
use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('verifyConfigArray() - it will set the config array properly', function(){
    $api_client = new ApiClientFake(null, [
        'api_scopes' => [
            'https://www.googleapis.com/auth/admin.directory.group',
            'https://www.googleapis.com/auth/contacts'
        ],
        'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
        'log_channels' => ['single'],
        'subject_email' => config('tests.connections.test.subject_email')
    ]);
    $directory_client = new Drive($api_client);
    expect($directory_client->connection_config['api_scopes'])->toBe([
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ])
        ->and($directory_client->connection_config['json_key_file_path'])->toBe(storage_path('keys/glamstack-google-workspace/test.json'));
});
