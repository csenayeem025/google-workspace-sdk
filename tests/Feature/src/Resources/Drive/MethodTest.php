<?php

namespace Glamstack\GoogleWorkspace\Tests\Feature\src\Resources\Drive;

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;
use Illuminate\Support\Str;

test('get() - it can list accessible drives', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->drive()->get('/drives',[
        'useDomainAdminAccess' => 'true'
    ]);
    expect($response->object)->toBeObject();
});

test('post() - it can create a new Google Drive', function (){
    $api_client = new ApiClientFake('test');
    $uuid = Str::uuid()->toString();
    $response = $api_client->drive()->post('/drives' . '?requestId=' . $uuid,
   [
       'name' => 'post-' . config('tests.connections.test.test_drive_name'),
   ]);

    expect($response->object->name)->toBe('post-' . config('tests.connections.test.test_drive_name'));

    $delete_response = $api_client->drive()->delete('/drives/' . $response->object->id);
    expect($delete_response->status->successful)->toBeTrue();
});

test('patch() - it can update a Google Drive', function(){
    $api_client = new ApiClientFake('test');
    $uuid = Str::uuid()->toString();
    $response = $api_client->drive()->post('/drives' . '?requestId=' . $uuid,
        [
            'name' => 'put-' . config('tests.connections.test.test_drive_name'),
        ]);

    expect($response->object->name)->toBe('put-' . config('tests.connections.test.test_drive_name'));

    $update_response = $api_client->drive()->patch('/drives/' . $response->object->id, [
       'name' => 'updated-put-' . config('tests.connections.test.test_drive_name')
    ]);

    expect($update_response->object->name)->toBe('updated-put-' . config('tests.connections.test.test_drive_name'));

    $delete_response = $api_client->drive()->delete('/drives/' . $response->object->id);
    expect($delete_response->status->successful)->toBeTrue();
});

test('delete() - it can delete a Google Drive', function(){
    $api_client = new ApiClientFake('test');
    $uuid = Str::uuid()->toString();
    $response = $api_client->drive()->post('/drives' . '?requestId=' . $uuid,
        [
            'name' => 'delete-' . config('tests.connections.test.test_drive_name'),
        ]);

    expect($response->object->name)->toBe('delete-' . config('tests.connections.test.test_drive_name'));

    $delete_response = $api_client->drive()->delete('/drives/' . $response->object->id);
    expect($delete_response->status->successful)->toBeTrue();
});
