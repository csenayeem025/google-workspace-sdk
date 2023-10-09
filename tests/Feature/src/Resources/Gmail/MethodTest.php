<?php

namespace Glamstack\GoogleWorkspace\Tests\Feature\src\Resources\Gmail;

use DateTime;
use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('get() - it can list email messages of a user', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->gmail()->get('/users/' .
        config('tests.connections.test.subject_email') . '/messages',
    [
        'maxResults' => 2
    ]);
    expect($response->object)->toBeObject()
        ->and($response->status->successful)->toBeTrue()
        ->and($response->status->code)->toBe(200);
});

test('get() - it can list all forwarding addresses for a user', function(){
    $api_clinet = new ApiClientFake('test');
    $response = $api_clinet->gmail()->get(
        '/users/' . config('tests.connections.test.subject_email') .
        '/settings/forwardingAddresses'
    );
    expect($response->status->successful)->toBeTrue();
});

test('post() - it can create a new forwarding address for a user', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->gmail()->post(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/forwardingAddresses',
        [
            'forwardingEmail' => 'post-' . config('tests.connections.test.subject_email'),
        ]
    );
    expect($response->object->forwardingEmail)->toBe('post-' . config('tests.connections.test.subject_email'));

    $remove_response = $api_client->gmail()->delete(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/forwardingAddresses/' .
        $response->object->forwardingEmail
    );
    expect($remove_response->status->successful)->toBeTrue();
});

test('put() - it can update a users vacation message' , function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->gmail()->put(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/vacation',
        [
            'responseSubject' => 'Glamstack PUT test',
            'responseBodyPlainText' => 'This message was set using Glamstack Google Workspace SDK.',

        ]
    );

    expect($response->object->responseSubject)->toBe('Glamstack PUT test');
    expect($response->object->responseBodyPlainText)->toBe('This message was set using Glamstack Google Workspace SDK.');

    $remove_response = $api_client->gmail()->put(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/vacation',
        [
            'endTime' => time()
        ]
    );
    expect($remove_response->status->successful)->toBeTrue();
});

test('delete() - it can delete a forwarding address of a user', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->gmail()->post(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/forwardingAddresses',
        [
            'forwardingEmail' => 'delete-' . config('tests.connections.test.subject_email'),
        ]
    );
    expect($response->object->forwardingEmail)->toBe('delete-' . config('tests.connections.test.subject_email'));

    $remove_response = $api_client->gmail()->delete(
        '/users/' . config('tests.connections.test.subject_email') . '/settings/forwardingAddresses/' .
        $response->object->forwardingEmail
    );
    expect($remove_response->status->successful)->toBeTrue();
});
