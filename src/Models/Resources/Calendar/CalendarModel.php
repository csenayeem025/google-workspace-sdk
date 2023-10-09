<?php

namespace Glamstack\GoogleWorkspace\Models\Resources\Calendar;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CalendarModel
{
    public function verifyConfigArray(array $options): array
    {
        $validator = Validator::make($options,
            [
                'api_scopes' => 'required|array',
                'subject_email' => 'nullable|string',
                'json_key_file_path' => Rule::requiredIf(!array_key_exists('json_key', $options)).'|string',
                'json_key' => Rule::requiredIf(!array_key_exists('json_key_file_path', $options)).'|string',
                'log_channels' => 'nullable|array'
            ],
            [
                'json_key_file_path.required' => 'Either the json_key_file_path or json_key parameters are required',
                'json_key.required' => 'Either the json_key_file_path or json_key parameters are required'
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->messages()->first(), 400);
        }

        return $options;
    }
}
