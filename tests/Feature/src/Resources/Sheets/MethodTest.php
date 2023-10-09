<?php

namespace Glamstack\GoogleWorkspace\Tests\Feature\src\Resources\Sheets;

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('get() - it can get sheet contents from A1 cell', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->sheets()->get(
        '/' . config('tests.connections.test.test_sheet_id') .
        '/values/A1',
    );
    expect($response->status->successful)->toBeTrue()
        ->and($response->object->values)->toBeArray()
        ->and($response->object->majorDimension)->toBe('ROWS');
});

test('post() - it can clear A2 Cell', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->sheets()->post(
        '/' . config('tests.connections.test.test_sheet_id') .
        '/values:batchClear',
        [
            'ranges' => [
                'A2'
            ]
        ]
    );
    expect($response->object->spreadsheetId)->toBe(config('tests.connections.test.test_sheet_id'))
        ->and($response->object->clearedRanges[0])->toBe('Sheet1!A2');
});

test('put() - it can update the A2 Cell', function(){
    $api_client = new ApiClientFake('test');
    $response = $api_client->sheets()->put(
        '/' .config('tests.connections.test.test_sheet_id') . '/values/A2?valueInputOption=USER_ENTERED',
        [
            'values' => [
                '0' => [
                    '0' => 'testing'
                ]
            ]
        ]
    );
    expect($response->object->updatedRange)->toBe('Sheet1!A2')
        ->and($response->status->successful)->toBeTrue()
        ->and($response->object->updatedRows)->toBe(1);
});
