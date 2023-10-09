<?php

namespace Glamstack\GoogleWorkspace\Tests\Feature\src\Resources\Calendar;

use Glamstack\GoogleWorkspace\Tests\Fakes\ApiClientFake;

test('get() - it can use GET to list calendars', function() {
    $api_client = new ApiClientFake('test');
    $response = $api_client->calendar()->get('/users/me/calendarList');
    expect($response->status->code)->toBe(200)
        ->and(property_exists($response->object, 'items'))->toBeTrue();
});

test('post() - it can use POST to create calendar event', function(){
    $api_client = new ApiClientFake('test');
    $start_date = now()->add('days', 1)->toDateString();
    $end_date = now()->add('days',2)->toDateString();
    $response = $api_client->calendar()->post(
        '/calendars/' . config('tests.connections.test.subject_email') . '/events',
        [
            'start' => [
                'date' => $start_date,
            ],
            'end' => [
                'date' => $end_date,
            ]
        ]
    );
    expect($response->status->successful)->toBeTrue()
        ->and($response->object->start->date)->toBe($start_date)
        ->and($response->object->end->date)->toBe($end_date);
});

test('put() - it can update a calendar event', function(){
    $api_client = new ApiClientFake('test');

    $start_date = now()->add('days', 2)->toDateString();
    $end_date = now()->add('days', 3)->toDateString();

    $get_response = $api_client->calendar()->get(
        '/calendars/' . config('tests.connections.test.subject_email') . '/events'
    );
    $first_event = collect($get_response->object->items)->first();
    $response = $api_client->calendar()->put(
        '/calendars/' . config('tests.connections.test.subject_email') . '/events/' . $first_event->id,
        [
            'start' => [
                'date' => $start_date,
            ],
            'end' => [
                'date' => $end_date,
            ]
        ]
    );
    expect($response->object->start->date)->toBe($start_date)
        ->and($response->object->end->date)->toBe($end_date);
});

test('delete() - it can delete a calendar event', function(){
    $api_client = new ApiClientFake('test');
    $start_date = now()->add('days', 1)->toDateString();
    $end_date = now()->add('days', 2)->toDateString();
    $create_response = $api_client->calendar()->post(
        '/calendars/' . config('tests.connections.test.subject_email') . '/events',
        [
            'start' => [
                'date' => $start_date,
            ],
            'end' => [
                'date' => $end_date,
            ]
        ]
    );
    $new_event_id = $create_response->object->id;
    $delete_response = $api_client->calendar()->delete(
        '/calendars/' . config('tests.connections.test.subject_email') . '/events/' . $new_event_id,
    );
    expect($delete_response->status->code)->toBe(204)
        ->and($delete_response->object)->toBeNull()
        ->and($delete_response->status->successful)->toBeTrue();
});
