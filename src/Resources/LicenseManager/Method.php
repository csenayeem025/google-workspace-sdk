<?php

namespace Glamstack\GoogleWorkspace\Resources\LicenseManager;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\BaseClient;

class Method extends BaseClient
{

    protected string $customer_id;

    public function __construct(ApiClient $api_client, string $auth_token)
    {
        parent::__construct($api_client, $auth_token);
        $this->setCustomerId();
    }

    /**
     * Set the project_id class level variable
     *
     * @return void
     */
    protected function setCustomerId(): void
    {
        if ($this->connection_key) {
            $this->customer_id = config(
                $this->config_path . '.connections.' .
                $this->connection_key . '.customer_id'
            );
        } else {
            $this->customer_id = $this->api_client->connection_config['customer_id'];
        }
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
        $request_data = $this->appendRequiredHeaders($request_data);

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
     * @param bool $exclude_customer
     *      Allow for excluding `customer` value in request
     *
     * @return object|string
     */
    public function post(string $url, ?array $request_data = [], bool $exclude_customer = false): object|string
    {
        $request_data = $this->appendRequiredHeaders($request_data, $exclude_customer);

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
        $request_data = $this->appendRequiredHeaders($request_data);

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
        $request_data = $this->appendRequiredHeaders($request_data);

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
        $request_data = $this->appendRequiredHeaders($request_data);

        return BaseClient::deleteRequest($url, $request_data);
    }

    /**
     * Append required headers to request_data
     *
     * The required headers for most Google Workspace License is the `customer`
     * variable.
     *
     * @param array $request_data
     *      The request data being passed into the HTTP request
     *
     * @param bool $exclude_customer
     *      Allow for excluding `customer` value in request
     *
     * @return array
     */
    protected function appendRequiredHeaders(array $request_data, bool $exclude_customer = false): array
    {

        if ($exclude_customer){
            $required_parameters = [
            ];
        } else {
            $required_parameters = [
                'customer_id' => $this->customer_id
            ];
        }

        return array_merge($request_data, $required_parameters);
    }
}
