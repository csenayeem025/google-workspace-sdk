<?php

namespace Glamstack\GoogleWorkspace\Resources\Gmail;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\BaseClient;

class Method extends BaseClient
{
    public function __construct(ApiClient $api_client, string $auth_token)
    {
        parent::__construct($api_client, $auth_token);
    }

    /**
     * Run generic GET request on Google URL
     *
     * @param string $url
     *      The URL to run the GET request on (i.e `https://admin.googleapis.com/admin/directory/v1/groups/<group_id>`)
     *
     * @param array $request_data
     *      Optional array data to pass into the GET request
     *
     * @return object|string
     */
    public function get(string $url, array $request_data = []): object|string
    {
        return BaseClient::getRequest($url, $request_data);
    }

    /**
     * Run generic POST request on Google URL
     *
     * @param string $url
     *      The URL to run the POST request on (i.e `https://admin.googleapis.com/admin/directory/v1/groups/<group_id>`)
     *
     * @param array|null $request_data
     *      Optional array data to pass into the POST request
     *
     * @return object|string
     */
    public function post(string $url, ?array $request_data = []): object|string
    {
        return BaseClient::postRequest($url, $request_data);
    }

    /**
     * Run generic PATCH request on Google URL
     *
     * @param string $url
     *      The URL to run the PATCH request on (i.e `https://admin.googleapis.com/admin/directory/v1/groups/<group_id>`)
     *
     * @param array $request_data
     *      Optional array data to pass into the PATCH request
     *
     * @return object|string
     */
    public function patch(string $url, array $request_data = []): object|string
    {
        return BaseClient::patchRequest($url, $request_data);
    }

    /**
     * Run generic PUT request on Google URL
     *
     * @param string $url
     *      The URL to run the PUT request on (i.e `https://admin.googleapis.com/admin/directory/v1/groups/<group_id>`)
     *
     * @param array $request_data
     *      Optional array data to pass into the PUT request
     *
     * @return object|string
     */
    public function put(string $url, array $request_data = []): object|string
    {
        return BaseClient::putRequest($url, $request_data);
    }

    /**
     * Run generic DELETE request on Google URL
     *
     * @param string $url
     *      The URL to run the DELETE request on (i.e `https://admin.googleapis.com/admin/directory/v1/groups/<group_id>`)
     *
     * @param array $request_data
     *      Optional array data to pass into the DELETE request
     *
     * @return object|string
     */
    public function delete(string $url, array $request_data = []): object|string
    {
        return BaseClient::deleteRequest($url, $request_data);
    }
}
