# Google Workspace SDK

## Overview

The Google Workspace SDK is an open source [Composer](https://getcomposer.org/) package created by [GitLab IT Engineering](https://about.gitlab.com/handbook/business-technology/engineering/) for use in the [GitLab Access Manager](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager) Laravel application for connecting to Google API endpoints for provisioning and deprovisioning of users, groups, group membership, and other related functionality.

> **Disclaimer:** This is not an official package maintained by the Google or GitLab product and development teams. This is an internal tool that we use in the GitLab IT department that we have open sourced as part of our company values.
>
> Please use at your own risk and create issues for any bugs that you encounter.
>
> We do not maintain a roadmap of community feature requests, however we invite you to contribute and we will gladly review your merge requests.

## Dependencies

**Note:** This package will require the `glamstack/google-auth-sdk` package in order to operate. This is already configured as a required package in the composer.json file and should be automatically loaded when installing this package.

> All configurations for this package by default will be configured under the `glamstack-google-workspace.php` file that will be loaded when this package is installed. For further guidance please see the [Installation docs](#installation)

### Maintainers

| Name                                                                   | GitLab Handle                                          |  
| ---------------------------------------------------------------------- | ------------------------------------------------------ |  
| [Dillon Wheeler](https://about.gitlab.com/company/team/#dillonwheeler) | [@dillonwheeler](https://gitlab.com/dillonwheeler)     |  
| [Jeff Martin](https://about.gitlab.com/company/team/#jeffersonmartin)  | [@jeffersonmartin](https://gitlab.com/jeffersonmartin) |  

### How It Works

The package utilizes the [glamstack/google-auth-sdk](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/gitlab-sdk) package for creating the [Google JWT Web Token](https://cloud.google.com/iot/docs/how-tos/credentials/jwts) to authenticate with [Google Workspace API's](https://developers.google.com/admin-sdk/directory/reference/rest#service:-admin.googleapis.com).

For more information on [glamstack/google-auth-sdk](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/gitlab-sdk) please see the [Google Auth SDK README.md](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-auth-sdk/-/blob/main/README.md).

This package is not intended to provide functions for every endpoint for [Google Workspace API's](https://developers.google.com/admin-sdk/directory/reference/rest#service:-admin.googleapis.com). The endpoints will be constructed on an as needed basis. If you wish to add any additional endpoints please see [CONTRIBUTING](CONTRIBUTING.md).

If the endpoint that you need is not created yet we have provided the REST class that can perform GET, POST, PUT, and DELETE requests to any endpoint that you find in the [Google Workspace API's](https://developers.google.com/admin-sdk/directory/reference/rest#service:-admin.googleapis.com) documentation and the class will handle the API response, error handling, and pagination for you.

> :warning: `PATCH` request are not currently working but will be implemented in the future.

> This package builds upon the simplicity of the Laravel HTTP Client that is powered by the Guzzle HTTP client to provide "last lines of code parsing" for [Google Workspace API's](https://developers.google.com/admin-sdk/directory/reference/rest#service:-admin.googleapis.com) responses to improve the developer experience.

```php
// Initialized Client with `connection_key` parameter
$google_workspace_api = new \Glamstack\GoogleWorkspace\ApiClient('workspace');
  
// Retrieves a paginated list of either deleted users or all users in a domain.  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list  
$records = $google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users');  
  
// Retrieves a paginated list of either deleted users or all users in a domain  
// with query parameters included.  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list#OrderBy  
// https://developers.google.com/admin-sdk/directory/v1/guides/search-users  
$records = $google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users',[  
    'maxResults' => '200',
    'orderBy' => 'EMAIL',
    'query' => [
        'orgDepartment' => 'Test Department'
    ]
]);  
  
// Get a specific user from Google Workspace  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/get  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/'.$user_key);  
  
// Create new Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/insert  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users#User  
$record = $google_workspace_api->rest()->post('https://admin.googleapis.com/admin/directory/v1/users', [
    'name' => [
        'familyName' => 'Libby',
        'givenName' => 'Kate'
    ],
    'password' => 'ac!dBurnM3ss3sWithTheB4$t',
    'primaryEmail' => 'klibby@example.com'
]);  
  
// Update an existing Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/update  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->rest()->put('https://admin.googleapis.com/admin/directory/v1/users/'.$user_key, [  
    'name' => [
        'givenName' => 'Libby-Murphy'
    ]
]);  
  
// Delete a Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/delete  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->rest()->delete('https://admin.googleapis.com/admin/directory/v1/users/'.$user_key);  
```

### Package Initialization

This package is initialized via a configuration see [How To Initialize With Configuration File](#how-to-initialize-with-configuration-file) for instructions on both initialization methods.

## Installation

### Requirements

| Requirement | Version |  
| ----------- | ------- |  
| PHP         | >=8.0   |  
| Laravel     | >=9.0   |  

### Add Composer Package

This package uses [Calendar Versioning](#calendar-versioning).

We recommend always using a specific version in your `composer.json` file and reviewing the [changelog](changelog/) to see the breaking changes in each release before assuming that the latest release is the right choice for your project.

```bash
composer require csenayeem025/google-workspace-sdk
```

> If you are contributing to this package, see [CONTRIBUTING](CONTRIBUTING.md) for instructions on configuring a local composer package with symlinks.

### Publish the configuration file

```bash
php artisan vendor:publish --tag=glamstack-google-workspace
```

### Version upgrades

If you have upgraded to a newer version of the package, you should back up your existing configuration file to avoid your custom configuration being overridden.

```bash
cp config/glamstack-google-workspace.php config/glamstack-google-workspace.php.bak

php artisan vendor:publish --tag=glamstack-google-workspace
```

### Calendar Versioning

The GitLab IT Engineering team uses a modified version of [Calendar Versioning (CalVer)](https://calver.org/) instead of [Semantic Versioning (SemVer)](https://semver.org/). CalVer has a YY (Ex. 2021 => 21) but having a version `21.xx` feels unintuitive to us. Since our team started this in 2021, we decided to use the last integer of the year only (2021 => 1.x, 2022 => 2.x, etc).

The version number represents the release date in `vY.M.D` format.

#### Why We Don't Use Semantic Versioning

1. We are continuously shipping to `main`/`master`/`production` and make breaking changes in most releases, so having semantic backwards-compatible version numbers is unintuitive for us.
1. We don't like to debate what to call our release/milestone and whether it's a major, minor, or patch release. We simply write code, write a changelog, and ship it on the day that it's done. The changelog publication date becomes the tagged version number (Ex. `2022-02-01` is `v2.2.1`). We may refer to a bigger version number for larger releases (Ex. `v2.2`), however this is only for monthly milestone planning and canonical purposes only. All code tags include the day of release (Ex. `v2.2.1`).
1. This allows us to automate using GitLab CI/CD to automate the version tagging process based on the date the pipeline job runs.
1. We update each of our project `composer.json` files that use this package to specific or new version numbers during scheduled change windows without worrying about differences and/or breaking changes with "staying up to date with the latest version". We don't maintain any forks or divergent branches.
1. Our packages use underlying packages in your existing Laravel application, so keeping your Laravel application version up-to-date addresses most security concerns.

## Initializing the SDK

Initialization of the API Client can be done either by passing in a (string) [connection_key](#connection-keys) or by passing in an (array) [connection_config](#dynamic-connection-config-array)

### Google API Authentication

The package utilizes the [glamstack/google-auth-sdk](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-auth-sdk) package for creating the [Google JWT Web Token](https://cloud.google.com/iot/docs/how-tos/credentials/jwts) to authenticate with [Google Cloud API endpoints](https://cloud.google.com/apis).

For more information on [glamstack/google-auth-sdk](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-auth-sdk) please see the [Google Auth SDK README.md](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-auth-sdk/-/blob/main/README.md).

### Connection Keys

We use the concept of **_connection keys_** that refer to a configuration array in `config/glamstack-google-workspace.php` that allows you to pre-configure one or more API connections.

Each connection key is associated with a GCP service account JSON key. This can be used to configure different auth scope connections and permissions to your GCP organization or different GCP project(s) depending on the API calls that you're using. This allows for least privilege for specific API calls, and you can also configure multiple connections with the same GCP project and different API tokens that have different permission levels.

#### Example Connection Key Initialization

```php
// Initialize the SDK using the `test` configuration from `glamstack-google-workspace.php`
$client = new Glamstack\GoogleWorkspace\ApiClient('test');
```

#### Example Connection Key Configuration

```php
return [
    'connections' => [
        'test' => [
            'api_scopes' => [
                'https://www.googleapis.com/auth/admin.directory.group',
                'https://www.googleapis.com/auth/admin.directory.user'
            ],
            'json_key_file_path' => storage_path(env('GOOGLE_WORKSPACE_TEST_JSON_KEY_FILE_PATH')),
            'log_channels' => ['single'],
            'customer_id' => env('GOOGLE_WORKSPACE_TEST_CUSTOMER_ID'),
            'domain' => env('GOOGLE_WORKSPACE_TEST_DOMAIN'),
            'subject_email' => env('GOOGLE_WORKSPACE_TEST_SUBJECT_EMAIL'),
            'test_group_email' => env('GOOGLE_WORKSPACE_TEST_GROUP_EMAIL')
        ],
    ]
]
```

### Dynamic Connection Config Array

If you don't want to pre-configure your connection and prefer to dynamically use connection variables that are stored in your database, you have the ability to pass in the required configurations via an array (See [Example Connection Config Array Initialization](#example-connection-config-array-initialization)) using the `connection_config` array in the second argument of the `ApiClient` construct method.

#### Required Parameters

| Key                  | Type   | Description                                                      |
|----------------------|--------|------------------------------------------------------------------|
| `api_scopes`         | array  | Array of the API Scopes needed for the APIs to be used           |
| `customer_id`        | string | The Google Workspace Customer ID                                 |
| `domain`             | string | The Google Workspace Domain the APIs will be used in             |
| `json_key_file_path` | string | Option 1 - Provide a file path to the `.json` key file           |
| `json_key`           | string | Option 2 - Provide the JSON key contents stored in your database |

#### Using a JSON Key File on your filesystem

```php
$client = new Glamstack\GoogleWorkspace\ApiClient(null, [
    'api_scopes' => [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ],
    'customer_id' => config('tests.connections.test.customer_id'),
    'domain' => config('tests.connections.test.domain'),
    'json_key_file_path' => storage_path('keys/glamstack-google-workspace/test.json'),
    'log_channels' => ['single'],
    'subject_email' => config('tests.connections.test.subject_email')
]);
```

#### Using a JSON Key String in your database

**Security Warning:** You should never commit your service account key (JSON contents) into your source code as a variable to avoid compromising your credentials for your GCP organization or projects.

It is recommended to convert the JSON key to a base 64 encoded string before encryption since this is the format used by the GCP Service Account API for the `privateKeyData` field.

```php
// Get service account from your model (`GoogleServiceAccount` is an example)
$service_account = \App\Models\GoogleServiceAccount::where('id', '123456')->firstOrFail();

// Get JSON key string from database column that has an encrypted value
$json_key_string = decrypt(json_decode($service_account->json_key));

$client = new \Glamstack\GoogleWorkspace\ApiClient(null, [
    'api_scopes' => [
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/contacts'
    ],
    'customer_id' => config('tests.connections.test.customer_id'),
    'domain' => config('tests.connections.test.domain'),
    'json_key' => $json_key_string,
    'log_channels' => ['single'],
    'subject_email' => config('tests.connections.test.subject_email')
]);
```

The example below shows the value of the JSON key that is stored in your database.

```php
// Get service account from your model (`GoogleServiceAccount` is an example)
$service_account = \App\Models\GoogleServiceAccount::where('id', '123456')->firstOrFail();

dd(decrypt(json_decode($service_account->json_key));
// {
//     "type": "service_account",
//     "project_id": "project_id",
//     "private_key_id": "key_id",
//     "private_key": "key_data",
//     "client_email": "xxxxx@xxxxx.iam.gserviceaccount.com",
//     "client_id": "123455667897654",
//     "auth_uri": "https://accounts.google.com/o/oauth2/auth",
//     "token_uri": "https://oauth2.googleapis.com/token",
//     "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
//     "client_x509_cert_url": "some stuff"
// }
```

### Custom Logging Configuration

By default, we use the `single` channel for all logs that is configured in your application's `config/logging.php` file. This sends all Google Workspace log messages to the `storage/logs/laravel.log` file.

If you would like to see Google Workspace logs in a separate log file that is easier to triage without unrelated log messages, you can create a custom log channel.  For example, we recommend using the value of `glamstack-google-workspace`, however you can choose any name you would like.

Add the custom log channel to `config/logging.php`.

```php  
    'channels' => [  
        // Add anywhere in the `channels` array  
        'glamstack-google-workspace' => [
            'name' => 'glamstack-google-workspace',
            'driver' => 'single',
            'level' => 'debug',
            'path' => storage_path('logs/glamstack-google-workspace.log')
        ]
    ],  
```  

Update the `channels.stack.channels` array to include the array key (ex.  `glamstack-google-workspace`) of your custom channel. Be sure to add `glamstack-google-workspace` to the existing array values and not replace the existing values.

```php  
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => [
                'single','slack', 'glamstack-google-workspace'
            ],
            'ignore_exceptions' => false
        ]
    ],  
```  

## REST API Request

You can make an API request to any of the resource endpoints in the [Google Workspace Admin SDK Directory Documentation](https://developers.google.com/admin-sdk/directory/reference/rest).

### Inline Usage

```php  
// Initialize the SDK  
$api_client = new \Glamstack\GoogleWorkspace\ApiClient('workspace');
$response = $api_client->rest()->get('https://admin.googleapis.com/admin/directory/v1/users');
```  

### GET Request

The endpoints when uiltizing the REST class will require the full URL of the endpoint.

For examples, the [List Google Workspace Users](https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list) API Documentation shows the endpoint

```bash  
GET https://admin.googleapis.com/admin/directory/v1/users  
```  

With the SDK, you use the get() method with the endpoint for [Google Workspace Users](https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/get).

```php  
$google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users');  
```  

You can also use variables or database models to get data for constructing your endpoints.

```php  
$endpoint = 'https://admin.googleapis.com/admin/directory/v1/users';  
$records = $google_workspace_api->rest()->get($endpoint);  
```  

Here are some more examples of using endpoints.

```php  
// Get a list of Google Workspace Users  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list  
$records = $google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users');  
  
// Get a specific Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/get  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->get('https://admin.googleapis.com/admin/directory/v1/users/' . $userKey);  
```  

### GET Requests with Query String Parameters

The second argument of a `get()` method is an optional array of parameters that is parsed by the SDK and the [Laravel HTTP Client](https://laravel.com/docs/8.x/http-client#get-request-query-parameters) and rendered as a query string with the `?` and `&` added automatically.

```php  
// Retrieves a paginated list of either deleted users or all users in a domain  
// with query parameters included.  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/list#OrderBy  
// https://developers.google.com/admin-sdk/directory/v1/guides/search-users  
$records = $google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users',[  
    'maxResults' => '200',
    'orderBy' => 'EMAIL'
]);  
  
// This will parse the array and render the query string  
// https://admin.googleapis.com/admin/directory/v1/users?maxResults='200'&orderBy='EMAIL'  
```  

### POST Requests

The `post()` method works almost identically to a `get()` request with an array of parameters, however the parameters are passed as form data using the `application/json` content type rather than in the URL as a query string. This is industry standard and not specific to the SDK.

You can learn more about request data in the [Laravel HTTP Client documentation](https://laravel.com/docs/8.x/http-client#request-data).

```php  
// Create new Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/insert  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users#User  
$record = $google_workspace_api->rest()->post('https://admin.googleapis.com/admin/directory/v1/users', [  
    'name' => [
        'familyName' => 'Libby',
        'givenName' => 'Kate'
    ],
    'password' => 'ac!dBurnM3ss3sWithTheB4$t',
    'primaryEmail' => 'klibby@example.com'
]);  
```  

### PUT Requests

The `put()` method is used for updating an existing record (similar to `PATCH` requests). You need to ensure that the ID of the record that you want to update is provided in the first argument (URI).

In most applications, this will be a variable that you get from your database or another location and won't be hard-coded.

```php  
// Update an existing Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/update  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->rest()->put('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key, [
    'name' => [
        'givenName' => 'Libby-Murphy'
    ]
]);  
```  

### DELETE Requests

The `delete()` method is used for methods that will destroy the resource based on the ID that you provide.

Keep in mind that `delete()` methods will return different status codes depending on the vendor (ex. 200, 201, 202, 204, etc). Google Workspace API's will return a `204` status code for successfully deleted resources.

```php  
// Delete a Google Workspace User  
// https://developers.google.com/admin-sdk/directory/reference/rest/v1/users/delete  
$user_key = 'klibby@example.com';  
$record = $google_workspace_api->rest()->delete('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
```  

### Class Methods

The examples above show basic inline usage that is suitable for most use cases. If you prefer to use classes and constructors, the example below will provide a helpful example.

```php  
<?php  
  
use Glamstack\GoogleWorkspace\ApiClient;  
  
class GoogleWorkspaceUserService  
{  
    protected $google_workspace_api;  
    public function __construct() {
        $this->google_workspace_api = new \Glamstack\GoogleWorkspace\ApiClient();
    }  
    
    public function listUsers(array $query = []) : object
    {
        $users = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users', $query);
        return $users->object;
    }
    
    public function getUser(string $user_key, array $query = []) : object
    {
        $user = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key, $query);
        return $user->object;
    } 
    
    public function storeUser(string $user_key, array $request_data = []) : object
    {
        $response = $this->google_workspace_api->rest()->post('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key, $request_data);
        return $response->object;
    }

    public function updateUser(string $user_key, array $request_data = []) : object
    {
        $response = $this->google_workspace_api->rest()->put('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key, $request_data);
        return $response->object;
    } 

    public function deleteUser(string $user_key) : bool
    {
        $response = $this->google_workspace_api->rest()->delete('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);
        return $response->status->successful;
    }
}  
```  

## API Responses

This SDK uses the GLAM Stack standards for API response formatting.

```php  
// API Request  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
  
// API Response  
$response->headers; // object  
$response->json; // json  
$response->object; // object  
$response->status; // object  
$response->status->code; // int (ex. 200)  
$response->status->ok; // bool  
$response->status->successful; // bool  
$response->status->failed; // bool  
$response->status->serverError; // bool  
$response->status->clientError; // bool  
```  

### API Response Headers

```php  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
$response->headers;  
```  

```json  
{  
    +"ETag": ""nMRgLWac8h8NyH7Uk5VvV4DiNp4uxXg5gNUd9YhyaJE/MgKWL9SwIVWCY7rRA988mR8yR-k""
    +"Content-Type": "application/json; charset=UTF-8"    
    +"Vary": "Origin X-Origin Referer"    
    +"Date": "Thu, 20 Jan 2022 16:36:03 GMT"    
    +"Server": "ESF"    
    +"Content-Length": "1257"    
    +"X-XSS-Protection": "0"    
    +"X-Frame-Options": "SAMEORIGIN"    
    +"X-Content-Type-Options": "nosniff"    
    +"Alt-Svc": "h3=":443"; ma=2592000,h3-29=":443"; ma=2592000,h3-Q050=":443"; ma=2592000,h3-Q046=":443"; ma=2592000,h3-Q043=":443"; ma=2592000,quic=":443"; ma=2592000; v="46,43""
}  
```  

#### API Response Specific Header

```php  
$headers = (array) $response->headers;  
$content_type = $headers['Content-Type'];  
```  

```bash  
application/json  
```  

### API Response JSON

```php  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
$response->json;  
```  

```json  
{
    "kind":"admin#directory#user","id":"1111111111111",
    "etag":"\"nMRgLWac8h8NyH7Uk5VvV4DiNp4uxXg5gNUd9YhyaJE\/MgKWL9SwIVWCY7rRA988mR8yR-k\"",
    "primaryEmail":"klibby@example.com",
    "name":{
        "givenName":"Kate",
        "familyName":"Libby",
        "fullName":"Kate Libby"
    },
    "isAdmin":true,
    "isDelegatedAdmin":false,
    "lastLoginTime":"2022-01-18T15:26:16.000Z",
    "creationTime":"2021-12-08T13:15:43.000Z",
    "agreedToTerms":true,
    "suspended":false,
    "archived":false,
    "changePasswordAtNextLogin":false,
    "ipWhitelisted":false,
    "emails":[
        {
            "address":"klibby@example.com",
            "type":"work"
        },
        {
            "address":"klibby@example.com",
            "primary":true
        },
        {
            "address":"klibby@example.com.test-google-a.com"
        }
    ],
    "phones":[
        {
            "value":"5555555555",
            "type":"work"
        }
    ],
    "languages":[
        {
            "languageCode":"en",
            "preference":"preferred"
        }
    ],
    "nonEditableAliases":[
        "klibby@example.com.test-google-a.com"
    ],
    "customerId":"C000aaaaa",
    "orgUnitPath":"\/",
    "isMailboxSetup":true,
    "isEnrolledIn2Sv":false,
    "isEnforcedIn2Sv":false,
    "includeInGlobalAddressList":true
} 
```  

### API Response Object

```php  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
$response->object;  
```  

```php  
{#1256  
  +"kind": "admin#directory#user"
  +"id": "1111111111111"  
  +"etag": ""nMRgLWac8h8NyH7Uk5VvV4DiNp4uxXg5gNUd9YhyaJE/MgKWL9SwIVWCY7rRA988mR8yR-k""  
  +"primaryEmail": "klibby@example.com"  
  +"name": {#1242  
    +"givenName": "Kate"    
    +"familyName": "Libby"    
    +"fullName": "Kate Libby"  
  }  
  +"isAdmin": true  
  +"isDelegatedAdmin": false  
  +"lastLoginTime": "2022-01-18T15:26:16.000Z"  
  +"creationTime": "2021-12-08T13:15:43.000Z"  
  +"agreedToTerms": true  
  +"suspended": false  
  +"archived": false  
  +"changePasswordAtNextLogin": false  
  +"ipWhitelisted": false  
  +"emails": array:3 [
    0 => {#1253  
      +"address": "klibby@example.com"      
      +"type": "work"    
    }  
    1 => {#1258  
      +"address": "klibby@example.com"      
      +"primary": true  
    }  
    2 => {#1259  
      +"address": "klibby@example.com.test-google-a.com"
    }  
  ]  
  +"phones": array:1 [    
    0 => {#1247  
      +"value": "5555555555"      
      +"type": "work"    
    }  
  ]  
  +"languages": array:1 [    
    0 => {#1250  
      +"languageCode": "en"      
      +"preference": "preferred"    
    }  
  ]
  +"nonEditableAliases": array:1 [  
    0 => "klibby@example-test.com.test-google-a.com"  
  ]  
  +"customerId": "C000aaaaa"  
  +"orgUnitPath": "/"  
  +"isMailboxSetup": true  
  +"isEnrolledIn2Sv": false  
  +"isEnforcedIn2Sv": false  
  +"includeInGlobalAddressList": true  
}  
```  

### API Response Status

See the [Laravel HTTP Client documentation](https://laravel.com/docs/8.x/http-client#error-handling) to learn more about the different status booleans.

```php  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
$response->status;  
```  

```php  
{  
  +"code": 200  
  +"ok": true  
  +"successful": true  
  +"failed": false  
  +"serverError": false  
  +"clientError": false  
}  
```  

#### API Response Status Code

```php  
$response = $this->google_workspace_api->rest()->get('https://admin.googleapis.com/admin/directory/v1/users/' . $user_key);  
$response->status->code;  
```  

```bash  
200  
```  

## Error Handling

The HTTP status code for the API response is included in each log entry in the message and in the JSON `status_code`. Any internal SDK errors also included an equivalent status code depending on the type of error. The `message` includes the SDK friendly message. If an exception is thrown, the `reference`

If a `5xx` error is returned from the API, the `GoogleWorkspaceApiClient` `handleException` method will return a response.

See the [Log Outputs](#log-outputs) below for how the SDK handles errors and logging.

## Log Outputs

> The output of error messages is shown in the `README` to allow search engines to index these messages for developer debugging support. Any 5xx error messages will be returned as as `Symfony\Component\HttpKernel\Exception\HttpException` or configuration errors, including any errors in the `ApiClient::setApiConnectionVariables()` method.

## Issue Tracking and Bug Reports

Please visit our [issue tracker](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-workspace-sdk/-/issues) and create an issue or comment on an existing issue.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) to learn more about how to contribute.-
