<?php

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('verifyConfigArray() - it throws exception if api_scopes is not an array', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => 'testing.api.scopes',
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key_file_path' => storage_path('not_real')
    ]);
})->expectExceptionMessage('The api scopes must be an array.');

test('verifyConfigArray() - it throws exception if customer_id is not a string', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => ['testing_id_array'],
        'domain' => 'testing-domain',
        'json_key_file_path' => storage_path('not real')
    ]);
})->expectExceptionMessage('The customer id must be a string');

test('verifyConfigArray() - it throws exception if domain is not a string', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => ['testing-domain', 'testing-domain-2'],
        'json_key_file_path' => storage_path('not real')
    ]);
})->expectExceptionMessage('The domain must be a string');

test('verifyConfigArray() - it throws exception if subject_email is not string', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key_file_path' => storage_path('a_file_path'),
        'subject_email' => ['subject_email@example.com', 'another_subject_email@example.com']
    ]);
})->expectExceptionMessage('The subject email must be a string');


test('verifyConfigArray() - it throws exception if json_key_file_path is not a string', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key_file_path' => [storage_path('a_file_path'), storage_path('second_file_path')],
    ]);
})->expectExceptionMessage('The json key file path must be a string');

test('verifyConfigArray() - it throws exception if json_key is not a string', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key' => ['fake json_key example', 'fake json_key example 2']
    ]);
})->expectExceptionMessage('The json key must be a string');

test('verifyConfigArray() - it throws exception if log_channels is not an array', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
        'json_key_file_path' => storage_path('a_file_path'),
        'log_channels' => 'single'
    ]);
})->expectExceptionMessage('The log channels must be an array');

test('verifyConfigArray() - it throws exception if neither json_key_file_path or json_key are set', function(){
    $client = new ApiClientFake(null, [
        'api_scopes' => ['testing.api.scopes'],
        'customer_id' => 'testing_id',
        'domain' => 'testing-domain',
    ]);
})->expectExceptionMessage('Either the json_key_file_path or json_key parameters are required');
