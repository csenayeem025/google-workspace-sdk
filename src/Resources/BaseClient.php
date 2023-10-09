<?php

namespace Glamstack\GoogleWorkspace\Resources;

use Exception;
use Glamstack\GoogleAuth\AuthClient;
use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Traits\ResponseLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class BaseClient
{
    use ResponseLog;

    protected array $log_channels;
    private string $auth_token;
    protected ApiClient $api_client;
    public string $config_path;
    public ?string $connection_key;
    private array $connection_config;
    private array $request_headers;

    /**
     * @throws Exception
     */
    public function __construct(
        ApiClient $api_client,
        string $auth_token
    )
    {
        if (empty($api_client->connection_config)) {
            $this->setConnectionKey($api_client->connection_key);
            $this->connection_config = [];
        } else {
            $this->connection_config = $api_client->connection_config;
            $this->connection_key = null;
        }
        $this->api_client = $api_client;
        $this->setConfigPath();
        $this->setLogChannels();
        $this->setRequestHeaders();
        // Initialize Google Auth SDK
        $this->auth_token = $auth_token;
    }
    /**
     * Set the config path
     */
    public function setConfigPath()
    {
        $this->config_path = env('GLAMSTACK_GOOGLE_WORKSPACE_CONFIG_PATH', 'glamstack-google-workspace');
    }
    /**
     * Google API GET Request
     *
     * @param string $url
     *      The URL of the Google Cloud API
     *
     * @param array $request_data
     *      (Optional) Optional request data to send with the Google Cloud
     *          API GET request
     *
     * @return object|string
     */
    public function getRequest(string $url, array $request_data = []): object|string
    {
        // Get the initial response
        $response = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->get($url, $request_data);

        // Check if the initial response is paginated
        $isPaginated = $this->checkForPagination($response);

        // If it is paginated
        if ($isPaginated) {

            // Get the paginated results
            $paginated_results = $this->getPaginatedResults($url, $request_data, $response);

            $response->results = $this->convertPaginatedResponseToObject($paginated_results);

            // Unset the body and json elements of the original Guzzle Response
            // Object. These will be reset with the paginated results.
            unset($response->body);
            unset($response->json);
        } else {
            // This if statement will catch if Google is sending back a response
            // for an endpoint that can be paginated but is not.
            // I.E Google Groups list endpoint but there is only a single group
            if (count(collect($response->object())) == 3 &&
                property_exists($response->object(), 'kind') &&
                property_exists($response->object(), 'etag')) {
                // Due to the formatting of the response, we are flattening the response object and converting it to an array
                // This is to remove the nested element that would be the list command i.e. `groups` element when listing groups.
                $response->results = $this->convertPaginatedResponseToObject(collect($this->getResponseBody($response))->flatten()->toArray());
            } else if($response->status() == 204 and $response->successful()){
                // If there is no content and the API was successful then return
                // an object that is null
                $response->results = (object) null;
            }
            else {
                // This will catch all GET request that are not possible to be paginated request.
                $response->results = $this->convertPaginatedResponseToObject(collect($this->getResponseBody($response))->toArray());
            }
        }

        // Parse the API response and return a Glamstack standardized response
        $parsed_api_response = $this->parseApiResponse($response, true);

        $this->logResponse($url, $parsed_api_response);
        return $parsed_api_response;
    }


    /**
     * Check if pagination is used in the Google Cloud GET response.
     *
     * @param Response $response
     *      API response from Google Cloud GET request
     *
     * @return bool True if pagination is required | False if not
     */
    protected function checkForPagination(Response $response): bool
    {
        if($response->object()){
            // Check if Google Cloud GET Request object contains `nextPageToken`
            if (property_exists($response->object(), 'nextPageToken')) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Helper method for getting Google Cloud GET responses that require
     * pagination
     *
     * @param string $url
     *      The URL of the Google Cloud API request with a leading slash after
     *          `https://admin.googleapis.com/admin/directory/v1`
     *
     * @param array $request_data
     *      Request data to send with the Google Cloud API GET request
     *
     * @param Response $response
     *      API response from Google Cloud GET request
     *
     * @return array
     */
    protected function getPaginatedResults(
        string   $url,
        array    $request_data,
        Response $response
    ): array
    {
        // Initialize $records as an empty array. This is where we will store
        // the returned data from each paginated request.
        $records = [];

        // Collect the response body from the initial GET request's response
        $response_body = collect($this->getResponseBody($response))->flatten();

        // Merge the initial GET request's response into the $records array
        $records = array_merge($records, $response_body->toArray());

        // Get the next page using the initial responses `nextPageToken` element
        $next_response = $this->getNextPageResults(
            $url,
            $request_data,
            $response
        );

        // Collect the response body from the subsequent GET request's response
        $next_response_body = collect(
            $this->getResponseBody($next_response)
        )->flatten();

        // Add the $next_response_body to the records array
        $records = array_merge($records, $next_response_body->toArray());

        // Check if there are more pages to GET
        $next_page_exists = $this->checkForPagination($next_response);

        if ($next_page_exists) {

            // If there is an additional (ex. third) page then continue through all
            // data until the API response does not contain the `nextPageToken`
            // element in the returned object
            do {
                $next_response = $this->getNextPageResults(
                    $url,
                    $request_data,
                    $next_response
                );

                // Collect the response body from the subsequent GET request's response
                $next_response_body = collect(
                    $this->getResponseBody($next_response)
                )->flatten();

                // Set the `next_response_body` to an array
                $next_response_body_array = $next_response_body->toArray();

                // Add the `next_response_body` array to the `records` array
                $records = array_merge($records, $next_response_body_array);

                // Check if there is another page
                $next_page_exists = $this->checkForPagination($next_response);
            } while ($next_page_exists);
        }
        return $records;
    }

    /**
     * Helper method to get just the response data from the Response object
     *
     * @param Response $response
     *      API response from Google Cloud GET request
     *
     * @return object
     */
    protected function getResponseBody(Response $response): object
    {
        // Check if the response object contains the `nextPageToken` element
        $contains_next_page = $this->checkForPagination($response);

        // Get the response object
        $response_object = $response->object();

        // If `resultSizeEstimate` property exists remove it
        if($response_object){
            if(property_exists($response->object(), 'resultSizeEstimate')){
                unset($response_object->resultSizeEstimate);
            }
        }

        // This if statement is to check if we are utilizing a possible paginated
        // end point. If so we remove the `kind` and `etag` properties`
        if ((count(collect($response_object)) == 3) || count(collect($response_object)) == 4 &&
            (property_exists($response_object, 'kind') &&
                property_exists($response_object, 'etag'))) {

            // Unset unnecessary elements
            unset($response_object->kind);
            unset($response_object->etag);
        }
        
        // If the response contains the `nextPageToken` element unset that
        if ($contains_next_page) {
            unset($response_object->nextPageToken);
        }

        return $response_object;
    }

    /**
     * Helper function to get the next page of a Google Cloud API GET
     * request.
     *
     * @param string $url
     *      The URL of the Google Cloud API request
     *
     * @param array $request_data
     *      Request data to send with the Google Cloud API GET request.
     *
     * @param Response $response
     *      API response from Google Cloud GET request
     *
     * @return Response
     */
    protected function getNextPageResults(
        string   $url,
        array    $request_data,
        Response $response
    ): Response
    {

        // Set the Google Cloud Query parameter `pageToken` to the
        // responses `nextPageToken` element
        $next_page = [
            'pageToken' => $this->getNextPageToken($response)
        ];

        // Merge the `request_data` with the `next_page` this tells Google
        // Cloud that we are working with a paginated response
        $request_body = array_merge($request_data, $next_page);

        $records = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->get($url, $request_body);

        $this->logHttpInfo('Success - Gathered the next page data', $records);

        return $records;
    }

    /**
     * Helper method to get the `nextPageToken` element from the GET Response
     * object
     *
     * @see https://cloud.google.com/apis/design/design_patterns#list_pagination
     *
     * @param Response $response
     *      Google Cloud API GET Request Guzzle response
     *
     * @return string
     */
    protected function getNextPageToken(Response $response): string
    {
        return $response->object()->nextPageToken;
    }

    /**
     * Convert paginated API response array into an object
     *
     * @param array $paginatedResponse
     *      Combined object returns from multiple pages of API responses
     *
     * @return object
     *      Object of the API responses combined.
     */
    protected function convertPaginatedResponseToObject(
        array $paginatedResponse
    ): object
    {
        $results = [];
        foreach ($paginatedResponse as $response_key => $response_value) {
            $results[$response_key] = $response_value;
        }
        return (object)$results;
    }

    /**
     * Parse the API response and return custom format for consistency
     *
     * Example Response:
     * ```php
     * {#1268
     *   +"headers": {#1216
     *     +"ETag": (truncated)
     *     +"Content-Type": "application/json; charset=UTF-8"
     *     +"Vary": "Origin X-Origin Referer"
     *     +"Date": "Mon, 24 Jan 2022 17:25:15 GMT"
     *     +"Server": "ESF"
     *     +"Content-Length": "1259"
     *     +"X-XSS-Protection": "0"
     *     +"X-Frame-Options": "SAMEORIGIN"
     *     +"X-Content-Type-Options": "nosniff"
     *     +"Alt-Svc": (truncated)
     *   }
     *   +"json": (truncated)
     *   +"object": {#1251
     *     +"kind": "admin#directory#user"
     *     +"id": "114522752583947996869"
     *     +"etag": (truncated)
     *     +"primaryEmail": "klibby@example.com"
     *     +"name": {#1248
     *       +"givenName": "Kate"
     *       +"familyName": "Libby"
     *       +"fullName": "Kate Libby"
     *     }
     *     +"isAdmin": true
     *     +"isDelegatedAdmin": false
     *     +"lastLoginTime": "2022-01-21T17:44:13.000Z"
     *     +"creationTime": "2021-12-08T13:15:43.000Z"
     *     +"agreedToTerms": true
     *     +"suspended": false
     *     +"archived": false
     *     +"changePasswordAtNextLogin": false
     *     +"ipWhitelisted": false
     *     +"emails": array:3 [
     *       0 => {#1260
     *         +"address": "klibby@example.com"
     *         +"type": "work"
     *       }
     *       1 => {#1259
     *         +"address": "klibby@example-test.com"
     *         +"primary": true
     *       }
     *       2 => {#1255
     *         +"address": "klibby@example.com.test-google-a.com"
     *       }
     *     ]
     *     +"phones": array:1 [
     *       0 => {#1214
     *         +"value": "5555555555"
     *         +"type": "work"
     *       }
     *     ]
     *     +"languages": array:1 [
     *       0 => {#1271
     *         +"languageCode": "en"
     *         +"preference": "preferred"
     *       }
     *     ]
     *     +"nonEditableAliases": array:1 [
     *       0 => "klibby@example.com.test-google-a.com"
     *     ]
     *     +"customerId": "C000nnnnn"
     *     +"orgUnitPath": "/"
     *     +"isMailboxSetup": true
     *     +"isEnrolledIn2Sv": false
     *     +"isEnforcedIn2Sv": false
     *     +"includeInGlobalAddressList": true
     *   }
     *   +"status": {#1269
     *     +"code": 200
     *     +"ok": true
     *     +"successful": true
     *     +"failed": false
     *     +"serverError": false
     *     +"clientError": false
     *   }
     * }
     * ```
     *
     * @see https://laravel.com/docs/8.x/http-client#making-requests
     *
     * @param object $response
     *      Response object from API results
     * @param bool $get_request
     *      Rather the request type is GET or not
     * @return object
     *      Custom response returned for consistency
     */
    protected function parseApiResponse(object $response, bool $get_request = false): object
    {
        return (object)[
            'headers' => $this->convertHeadersToObject($response->headers()),
            'json' => $get_request == true ? json_encode($response->results) : json_encode($response->json()),
            'object' => $get_request == true ? (object)$response->results : $response->object(),
            'status' => (object)[
                'code' => $response->status(),
                'ok' => $response->ok(),
                'successful' => $response->successful(),
                'failed' => $response->failed(),
                'serverError' => $response->serverError(),
                'clientError' => $response->clientError(),
            ],
        ];

    }

    /**
     * Convert API Response Headers to Object
     * This method is called from the parseApiResponse method to prettify the
     * Guzzle Headers that are an array with nested array for each value, and
     * converts the single array values into strings and converts to an object
     * for easier and consistent accessibility with the parseApiResponse format.
     *
     * Example $header_response:
     * ```php
     * [
     *   "ETag" => [
     *     ""nMRgLWac8h8NyH7Uk5VvV4DiNp4uxXg5gNUd9YhyaJE/dky_PFyA8Zq0WLn1WqUCn_A8oes""
     *   ]
     *   "Content-Type" => [
     *     "application/json; charset=UTF-8"
     *   ]
     *   "Vary" => [
     *     "Origin"
     *     "X-Origin"
     *     "Referer"
     *   ]
     *   "Date" => [
     *      "Mon, 24 Jan 2022 15:39:46 GMT"
     *   ]
     *   "Server" => [
     *     "ESF"
     *   ]
     *   "Content-Length" => [
     *     "355675"
     *   ]
     *   "X-XSS-Protection" => [
     *     "0"
     *   ]
     *   "X-Frame-Options" => [
     *     "SAMEORIGIN"
     *   ]
     *   "X-Content-Type-Options" => [
     *     "nosniff"
     *   ]
     *   "Alt-Svc" => [
     *     (truncated)
     *   ]
     * ]
     * ```
     *
     * Example return object:
     * ```php
     * {#51667
     *   +"ETag": ""nMRgLWac8h8NyH7Uk5VvV4DiNp4uxXg5gNUd9YhyaJE/dky_PFyA8Zq0WLn1WqUCn_A8oes""
     *   +"Content-Type": "application/json; charset=UTF-8"
     *   +"Vary": "Origin X-Origin Referer"
     *   +"Date": "Mon, 24 Jan 2022 15:39:46 GMT"
     *   +"Server": "ESF"
     *   +"Content-Length": "355675"
     *   +"X-XSS-Protection": "0"
     *   +"X-Frame-Options": "SAMEORIGIN"
     *   +"X-Content-Type-Options": "nosniff"
     *   +"Alt-Svc": (truncated)
     * }
     * ```
     *
     * @param array $header_response
     *
     * @return object
     */
    protected function convertHeadersToObject(array $header_response): object
    {
        $headers = [];

        foreach ($header_response as $header_key => $header_value) {
            $headers[$header_key] = implode(" ", $header_value);
        }

        return (object)$headers;
    }

    /**
     * Google Cloud API POST Request. Google will utilize POST request for
     * inserting a new resource.
     *
     * This method is called from other services to perform a POST request and
     * return a structured object.
     *
     * @param string $url
     *      The URL of the Google Cloud API request with
     *
     * @param array|null $request_data
     *      (Optional) Optional request data to send with the Google Cloud API
     *          POST request
     *
     * @return object|string
     */
    public function postRequest(string $url, ?array $request_data = []): object|string
    {
        $request = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->post($url, $request_data);

        // Parse the API request's response and return a Glamstack standardized
        // response
        $response = $this->parseApiResponse($request);

        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Google Cloud API PATCH Request
     *
     * @param string $url
     *      The URL of the Google Cloud API request with
     *
     * @param array $request_data
     *      (Optional) Optional request data to send with the Google Cloud API
     *          PATCH request
     *
     * @return object|string
     */
    public function patchRequest(string $url, array $request_data = []): object|string
    {
        $request = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->patch($url, $request_data);

        // Parse the API request's response and return a Glamstack standardized
        // response
        $response = $this->parseApiResponse($request);

        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Google Cloud API PUT Request.
     *
     * This method is called from other services to perform a PUT request and
     * return a structured object
     *
     * @param string $url
     *      The URL of the Google Cloud API request
     *
     * @param array $request_data
     *      (Optional) Optional request data to send with the Google Cloud API
     *          PUT request
     *
     * @return object|string
     */
    public function putRequest(string $url, array $request_data = []): object|string
    {
        $request = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->put($url, $request_data);

        // Parse the API request's response and return a Glamstack standardized
        // response
        $response = $this->parseApiResponse($request);

        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Google Cloud API DELETE Request
     *
     * This method is called from other services to perform a DELETE request
     * and return a structured object.
     *
     * @param string $url
     *      The URL of the Google Cloud API request
     *
     * @param array $request_data
     *      (Optional) Optional request data to send with the Google Cloud API
     *          DELETE request
     *
     * @return object|string
     */
    public function deleteRequest(string $url, array $request_data = []): object|string
    {
        $request = Http::withToken($this->auth_token)
            ->withHeaders($this->request_headers)
            ->delete($url, $request_data);

        // Parse the API request's response and return a Glamstack standardized
        // response
        $response = $this->parseApiResponse($request);

        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Get the log_channels class level variable
     *
     * @return array
     */
    protected function getLogChannels(): array
    {
        return $this->log_channels;
    }

    /**
     * Set the log_channels class variable
     *
     * @return void
     */
    protected function setLogChannels(): void
    {
        if ($this->api_client->connection_key) {
            $this->log_channels = config(
                $this->config_path . '.connections.' .
                $this->connection_key . '.log_channels'
            );
        } else {
            $this->log_channels = $this->api_client->connection_config['log_channels'];
        }
    }
    protected function setConnectionKey(?string $connection_key): void
    {
        if ($connection_key == null) {
            $this->connection_key = config(
                $this->config_path . '.default.connection'
            );
        } else {
            $this->connection_key = $connection_key;
        }
    }


    /**
     * Set the request headers for the Google Cloud API request
     *
     * @return void
     */
    protected function setRequestHeaders(): void
    {
        // Get Laravel and PHP Version
        $laravel = 'laravel/' . app()->version();
        $php = 'php/' . phpversion();

        // Decode the composer.lock file
        $composer_lock_json = json_decode(
            (string)file_get_contents(base_path('composer.lock')),
            true
        );

        // Use Laravel collection to search for the package. We will use the
        // array to get the package name (in case it changes with a fork) and
        // return the version key. For production, this will show a release
        // number. In development, this will show the branch name.
        /** @phpstan-ignore-next-line */
        $composer_package = collect($composer_lock_json['packages'])
            ->where('name', 'glamstack/google-workspace-sdk')
            ->first();

        /** @phpstan-ignore-next-line */
        if ($composer_package) {
            $package = $composer_package['name'] . '/' . $composer_package['version'];
        } else {
            $package = 'dev-google-workspace-sdk';
        }

        // Define request headers
        $this->request_headers = [
            'User-Agent' => $package . ' ' . $laravel . ' ' . $php
        ];
    }

}
