<?php

namespace Glamstack\GoogleWorkspace\Resources\LicenseManager;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Models\Resources\LicenseManager\LicenseManagerModel;

class LicenseManager extends ApiClient
{
    public const BASE_URL = "https://licensing.googleapis.com/apps/licensing/v1/product";

    protected string $auth_token;

    public function __construct(ApiClient $api_client)
    {
        $license_model = new LicenseManagerModel();

        if(empty($api_client->connection_config)){
            $this->setConnectionKey($api_client->connection_key);
            $this->connection_config = [];
        } else {
            $this->connection_config = $license_model->verifyConfigArray($api_client->connection_config);
            $this->connection_key = null;
        }

        if(!$api_client->auth_token){
            parent::__construct($api_client->connection_key, $api_client->connection_config);
        }

        $this->auth_token = $api_client->auth_token;
    }

    /**
     * GET HTTP Request
     *
     * This will perform a GET request against the provided `url`. There is no
     * validation for the provided URL or request data in this method. (i.e.
     * `https://admin.googleapis.com/admin/directory/v1/groups`)
     *
     * @param string $url
     *      The Google URL to run the GET request with
     *
     * @param array $request_data
     *      Request data to load into GET request `Request Body`
     *
     * @return object|string
     *
     * @throws Exception
     */
    public function get(string $url, array $request_data = []): object|string
    {
        $method = new Method($this, $this->auth_token);
        return $method->get(self::BASE_URL . $url, $request_data);
    }

    /**
     * POST HTTP Request
     *
     * This will perform a POST request against the provided `url`. There is no
     * validation for the provided URL or request data in this method. (i.e
     * `https://admin.googleapis.com/admin/directory/v1/groups`)
     *
     * @param string $url
     *      The Google URL to run the POST request with
     *
     * @param array|null $request_data
     *      Request data to load into POST request `Request Body`
     *
     * @param bool $exclude_customer
     *      Allow for excluding `customer` value in request*
     *
     * @return object|string
     *
     * @throws Exception
     */
    public function post(string $url, ?array $request_data = [], bool $exclude_customer = false): object|string
    {
        $method = new Method($this, $this->auth_token);
        return $method->post(self::BASE_URL . $url, $request_data, $exclude_customer);
    }

    /**
     * PATCH HTTP Request
     *
     * This will perform a PATCH request against the provided `url`. There is no
     * validation for the provided URL or request data in this method. (i.e
     * `https://admin.googleapis.com/admin/directory/v1/groups`)
     *
     * @param string $url
     *      The Google URL to run the PATCH request with
     *
     * @param array $request_data
     *      Request data to load into PATCH request `Request Body`
     *
     * @return object|string
     *
     * @throws Exception
     */
    public function patch(string $url, array $request_data = []): object|string
    {
        $method = new Method($this, $this->auth_token);
        return $method->patch(self::BASE_URL . $url, $request_data);
    }

    /**
     * PUT HTTP Request
     *
     * This will perform a PUT request against the provided `url`. There is no
     * validation for the provided URL or request data in this method. (i.e
     * `https://admin.googleapis.com/admin/directory/v1/groups`)
     *
     * @param string $url
     *      The Google URL to run the PUT request with
     *
     * @param array $request_data
     *      Request data to load into PUT request `Request Body`
     *
     * @return object|string
     *
     * @throws Exception
     */
    public function put(string $url, array $request_data = []): object|string
    {
        $method = new Method($this, $this->auth_token);
        return $method->put(self::BASE_URL . $url, $request_data);
    }

    /**
     * DELETE HTTP Request
     *
     * This will perform a DELETE request against the provided `url`. There is no
     * validation for the provided URL or request data in this method. (i.e
     * `https://admin.googleapis.com/admin/directory/v1/groups`)
     *
     * @param string $url
     *      The Google URL to run the DELETE request with
     *
     * @param array $request_data
     *      Request data to load into DELETE request `Request Body`
     *
     * @return object|string
     *
     * @throws Exception
     */
    public function delete(string $url, array $request_data = []): object|string
    {
        $method = new Method($this, $this->auth_token);
        return $method->delete(self::BASE_URL . $url, $request_data);
    }
}
