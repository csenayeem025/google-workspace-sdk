<?php

namespace Glamstack\GoogleWorkspace\Tests\Feature\src\Resources\Directory;

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('get() - it can use GET to access a single groups', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->get('/groups/' . config('tests.connections.test.test_group_email'));
    expect($response->status->code)->toBe(200);
});

test('get() - it can use GET to list group', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->get('/groups', [
        'maxResults' => 1
    ]);
    expect($response->status->code)->toBe(200);
});

test('get() - it can use GET list groups with a filter and have the same response', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->get('/groups', [
        'query' => 'email=' . config('tests.connections.test.test_group_email')
    ]);
    expect($response->status->code)->toBe(200);
});

test('post() - it can use POST to create a new group', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->post('/groups', [
        'email' => 'post-' . config('tests.connections.test.test_group_email')
    ]);
    expect($response->status->successful)->toBeTrue();
    expect($response->object->email)->toBe('post-' . config('tests.connections.test.test_group_email'));
    $delete_response = $api_client->directory()->delete('/groups/' . 'post-' . config('tests.connections.test.test_group_email'));
    expect($delete_response->status->successful)->toBeTrue();
});

test('put() - it can use PUT to update a group', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->post('/groups', [
        'email' => 'put-' . config('tests.connections.test.test_group_email')
    ]);
    expect($response->status->successful)->toBeTrue();
    expect($response->object->email)->toBe('put-' . config('tests.connections.test.test_group_email'));

    $update_response = $api_client->directory()->put('/groups/' . 'put-' . config('tests.connections.test.test_group_email'),
        [
            'name' => 'updated name'
        ]
    );

    expect($update_response->object->name)->toBe('updated name');

    $delete_response = $api_client->directory()->delete('/groups/' . 'put-' . config('tests.connections.test.test_group_email'));
    expect($delete_response->status->successful)->toBeTrue();
});

test('delete() - it can use DELETE to delete a group', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->directory()->post('/groups', [
        'email' => 'delete-' . config('tests.connections.test.test_group_email')
    ]);
    expect($response->status->successful)->toBeTrue();
    expect($response->object->email)->toBe('delete-' . config('tests.connections.test.test_group_email'));
    sleep(1);
    $delete_response = $api_client->directory()->delete('/groups/' . 'delete-' . config('tests.connections.test.test_group_email'));
    expect($delete_response->status->successful)->toBeTrue();
});
