<?php

namespace Glamstack\GoogleWorkspace\Resources\Calendar;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\BaseClient;

class Method extends BaseClient
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected string $domain;
    protected string $customer_id;

    public function __construct(ApiClient $api_client, string $auth_token)
    {
        parent::__construct($api_client, $auth_token);
        $this->setDomain();
        $this->setCustomerId();
    }

    /**
     * Set the project_id class level variable
     *
     * @return void
     */
    protected function setDomain(): void
    {
        if ($this->connection_key) {
            $this->domain = config(
                $this->config_path . '.connections.' .
                $this->connection_key . '.domain'
            );
        } else {
            $this->domain = $this->api_client->connection_config['domain'];
        }
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
     *      The URL to run the GET request on
     *
     * @param array $request_data
     *      Optional array data to pass into the GET request
     *
     * @param bool $exclude_domain
     *      Exclude the domain parameter from the GET request
     *
     * @param bool $exclude_customer
     *      Exclude the customerId parameter from the GET request
     * @return object|string
     */
    public function get(string $url, array $request_data = [], bool $exclude_domain = false, bool $exclude_customer = false): object|string
    {
        $request_data = $this->appendRequiredHeaders($request_data, $exclude_domain, $exclude_customer);

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

    /**
     * Append required headers to request_data
     *
     * The typical required headers for Google Workspace are the `domain` and `customer`
     * variables. However, there is the option to exclude them if necessary.
     *
     * @param array $request_data
     *      The request data being passed into the HTTP request
     *
     * @param bool $exclude_domain
     *      Remove the `domain` parameter from the GET request header
     *
     * @param bool $exclude_customer
     *      Remove the `customer` parameter from the GET request header
     **
     * @return array
     */
    protected function appendRequiredHeaders(array $request_data, bool $exclude_domain = false, bool $exclude_customer = false): array
    {
        if($exclude_customer){
            $required_parameters = [
                'domain' => $this->domain,
            ];
        } elseif ($exclude_domain){
            $required_parameters = [
                'customer' => $this->customer_id
            ];
        } else {
            $required_parameters = [
                'domain' => $this->domain,
                'customer' => $this->customer_id
            ];
        }

        return array_merge($request_data, $required_parameters);
    }
}
