<?php

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;
use Glamstack\GoogleWorkspace\Tests\Fakes\Resources\Directory\MethodFake;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('checkForPagination() - it returns true for paginated response from GET request', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'type'       => 'example',
            'id'         => 'some id',
            'attributes' => [
                'email'      => 'some email',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
            'nextPageToken' => 'asd'
        ], 200, []),
    ]);
    $test = Http::get('*');
    $paginated = $method_client->checkForPagination($test);
    expect($paginated)->toBeTrue();
});

test('checkForPagination() - it returns false for non-paginated response from GET request', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'type'       => 'example',
            'id'         => 'some id',
            'attributes' => [
                'email'      => 'some email',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
        ], 200, []),
    ]);
    $test = Http::get('*');
    $paginated = $method_client->checkForPagination($test);
    expect($paginated)->toBeFalse();
});

test('getResponseBody() - it can get the response body', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'kind'       => 'admin#directory#group',
            'id'         => '99999999',
            'attributes' => [
                'email'      => 'klibbygroup@exmaple.com',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
        ], 200, []),
    ]);
    $response = Http::get('*');
    $response_object = $method_client->getResponseBody($response);
    expect($response_object->id)->toBe('99999999');
    expect($response_object->attributes->email == 'klibbygroup@exmaple.com');
    expect(!property_exists($response_object, 'kind'))->toBeTrue();
    expect(!property_exists($response_object, 'etag'))->toBeTrue();
});

test('getResponseBody() - it can get the response body and unset nextPageToken', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'kind'       => 'admin#directory#group',
            'etag'       => 'etagid',
            'id'         => '99999999',
            'attributes' => [
                'email'      => 'klibbygroup@exmaple.com',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
            'nextPageToken' => 'nextpagetokenid'
        ], 200, []),
    ]);
    $response = Http::get('*');
    $response_object = $method_client->getResponseBody($response);
    expect($response_object->id)->toBe('99999999');
    expect($response_object->attributes->email == 'klibbygroup@exmaple.com');
    expect(!property_exists($response_object, 'nextPageToken'))->toBeTrue();
});
test('convertPaginatedResponseToObject() - it can convert paginated response to an object', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    $one = (object)[
        'username' => 'klibby@example.com',
        'phone_number' => '9999999999'
    ];
    $two = (object)[
        'username' => 'kbilly@example.com',
        'phone_number' => '9999999991'
    ];
    $response_example[0] = $one;
    $response_example[1] = $two;
    $paginated_object = $method_client->convertPaginatedResponseToObject($response_example);
    expect($paginated_object)->toBeObject();
    expect(collect($paginated_object)->flatten())->first()->username->toBe('klibby@example.com');
});

test('parseApiResponse() - it can parse non GET HTTP Responses', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'kind'       => 'admin#directory#group',
            'id'         => '99999999',
            'attributes' => [
                'email'      => 'klibbygroup@exmaple.com',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Server' => 'ESF'
        ]),
    ]);
    $response = Http::get('*');
    $parsed_response = $method_client->parseApiResponse($response);
    expect($parsed_response->object->id)->toBe('99999999');
    expect($parsed_response->status->code)->toBe(200);
});

test('parseApiResponse() - it can parse GET HTTP Responses', function(){
    $api_client = new ApiClientFake('test');
    $method_client = new MethodFake($api_client, 'fake_token');
    $method_client->setUp();
    Http::fake([
        '*' => Http::response([
            'kind'       => 'admin#directory#group',
            'id'         => '99999999',
            'attributes' => [
                'email'      => 'klibbygroup@example.com',
                'uuid'       => 'some uuid',
                'created_at' => 'some created_at',
                'updated_at' => 'some updated_at',
            ],
            'nextPageToken' => 'nextpagetokenid'
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Server' => 'ESF'
        ]),
    ]);
    $response = Http::get('*');
    $response->results = $method_client->convertPaginatedResponseToObject(collect($method_client->getResponseBody($response))->toArray());
    $parsed_response = $method_client->parseApiResponse($response, true);
    expect($parsed_response->object->id)->toBe('99999999');
    expect($parsed_response->object->attributes->email)->toBe('klibbygroup@example.com');
});

test('it will throw exception and log if bad initailization', function(){
        $api_client = new ApiClientFake('test_with_incorrect_permissions');
        $method_client = new MethodFake($api_client, 'fake_token');
        $method_client->setUp();
})->expectExceptionCode(400);
