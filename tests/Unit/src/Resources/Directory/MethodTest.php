<?php

namespace Glamstack\GoogleWorkspace\Tests\Unit\src\Resources\Directory;

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;
use Glamstack\GoogleWorkspace\Tests\Fakes\Resources\Directory\MethodFake;

test('setCustomerId() - it can set a customer ID from a connection key', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    expect($method_client->getCustomerId())->toBe(config('tests.connections.test.customer_id'));
});

test('setCustomerId() - it can set a customer ID from a connection config array', function(){
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
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    expect($method_client->getCustomerId())->toBe(env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'));
});
test('setDomain() - it can set the domain from connection key', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    expect($method_client->getDomain())->toBe(config('tests.connections.test.domain'));
});

test('setDomain() - it can set the domain from connection config array', function(){
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
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    expect($method_client->getDomain())->toBe(config('tests.connections.test.domain'));
});


test('appendRequiredHeaders() - it appends required headers', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    $headers = $method_client->appendRequiredHeaders([
        'example_name_name' => 'test-header'
    ]);
    expect($headers)->toBe([
        'example_name_name' => 'test-header',
        'domain' => config('tests.connections.test.domain'),
        'customer' => config('tests.connections.test.customer_id')
    ]);
});

test('appendRequiredHeaders() - it appends only domain', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    $headers = $method_client->appendRequiredHeaders(['example_name_name' => 'test-header'], false, true);
    expect($headers)->toBe([
        'example_name_name' => 'test-header',
        'domain' => config('tests.connections.test.domain'),
    ]);
});

test('appendRequiredHeaders() - it appends only customer', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    $headers = $method_client->appendRequiredHeaders(['example_name_name' => 'test-header'], true, false);
    expect($headers)->toBe([
        'example_name_name' => 'test-header',
        'customer' => config('tests.connections.test.customer_id')
    ]);
});
