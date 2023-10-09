# Contributing Guide

This project is in early development and not all processes have been formalized yet.

Please consider these to be guidelines. If in doubt, please create an issue and tag the [maintainers](README.md#maintainers) to discuss.

## Feature Requests and Ideas

Please [create an issue](https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-workspace-sdk/-/issues) and describe what you'd like to see. Since this project is designed as an internal tool, we will help where we can but no guarantees.

## Code Contributions

Please create an issue first to document the purpose of the contribution from a changelog and release notes perspective. After the issue is created, create a merge request from inside the issue, then checkout the branch that was created automatically for the issue and merge request. By creating the merge request from inside the issue, everything stays connected automatically and there are no name disparities.

Due to the volume of commits in merge requests, MR comments are easy to overlook. Please have any discussions in the comments of the issue when possible.

All merge requests can be assigned to one or all of the maintainers at your discretion. It is helpful to comment in the issue when you're ready to merge with any context that the maintainer/reviewer should know or be on the look out for.

## Environment Configuration

### Configuring Your Development Environment with Working Copies of Packages

When you run `composer install`, you will get the latest copy of the packages from the GitHub and GitLab repositories. However, you won't be able to see real-time changes if you change any code in the packages.

You can mitigate this problem by creating a local symlink (with resolved namespaces) for the package inside of your application that you're using for development and testing. By symlinking the packages into the newly created `packages` directory, you'll be able to preview and test your work without doing any Git commits (bad practice).

```bash
# Pre-Requisite (you should already have this)
# You can use any directory you want (if not using ~/Sites)
cd ~/Sites
git clone https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/packages/composer/google-workspace-sdk.git
git clone https://gitlab.com/gitlab-com/business-technology/engineering/access-manager/gitlab-access-workspace-app.git
```

```bash
cd ~/Sites/gitlab-access-manager-app
mkdir -p packages/glamstack
cd packages/glamstack
ln -s ~/Sites/google-workspace-sdk google-workspace-sdk
```

### Application Composer

Update the `composer.json` file in your testing application (not the package) to add the package to the `autoload.psr-4` array (append the array, don't replace anything).

```json
# ~/Sites/gitlab-access-manager-app/composer.json

"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Glamstack\\GoogleWorkspace\\": "packages/glamstack/google-workspace-sdk/src"
    }
},
```

### Configure Local Composer Repository

Credit: <https://laravel-news.com/developing-laravel-packages-with-local-composer-dependencies>

```bash
cd ~/Sites/gitlab-access-manager-app

composer config repositories.local '{"type": "path", "url": "packages/glamstack/google-workspace-sdk"}' --file composer.json

composer require glamstack/google-workspace-sdk

# Package operations: 1 install, 0 updates, 0 removals
#  - Installing glamstack/google-workspace-sdk (dev-1-add-package-scaffolding): Symlinking from packages/glamstack/google-workspace-sdk
```

### Caching Problems

If you run into any classes or files that are renamed and are throwing `Not Found` errors, you may need to use the `composer dump-autoload` command.

### Adding Additional Endpoints

The way that we have configured the package allows for additional endpoint to be added quickly by doing the following:

1. If there is no directory for the product group (i.e. [Directory](https://developers.google.com/admin-sdk/directory/reference/rest), [License Manager](https://developers.google.com/admin-sdk/licensing/reference/rest), [GMail](https://developers.google.com/gmail/api/reference/rest), [Drive](https://developers.google.com/drive/api/v3/reference), etc.) under the `src/Resources` directory then create the directory under `src/Resources` with the name of the group (i.e. `Directory`, `LicenseManager`, `Gmail`, `Drive`, etc.).
    1. After creating a directory for the product group you will need to create a class for the product group as well. This class will have the exact same name as the directory name. (i.e. The `Directory` group will have a file named `Directory.php` with a `Directory` class in it.)
    1. Things you will need to know for the class:
        * The base_url for the group endpoints. (i.e. Directory will have the BASE_URL of `https://admin.googleapis.com/admin/directory/v1`)
        > Note: The trailing `/` is not a part of the base_url
    1. Reference [Creating An Provider Group Class](#creating-an-provider-group-class) for template of an endpoint group.

1. If there is no class for the endpoint group you are wanting but the Product Group exists. Create a new PHP class under the appropriate `src/<directory>/` with the name of the endpoint group that it will be used for. For example if we were to create a new class to utilize the [Google Groups](https://developers.google.com/admin-sdk/directory/reference/rest/v1/groups) resource the file name will be `Groups.php` under the `src/Resources/Directory` directory

So the file path for [Google Groups Endpoints](https://developers.google.com/admin-sdk/directory/reference/rest/v1/groups) will be:
```bash
src/Resources/Directory/Groups.php
```
1. Utilize the [Endpoint Group Creation Template](#endpoint-group-creation-template) to create a new endpoint group class under the appropriate product directory.
1. Add a new method for the class under the product group class. See [Adding An Endpoint Group to Product Group Class](#how-to-add-an-endpoint-group-to-product-group-class)
1. Utilize the [Endpoint Method Creation Template](#endpoint-method-creation-template) to create a new endpoint method in the class.


#### Creating An Product Group Class

The purpose of the product group is to allow for a smoother developer experience when using the SDK. This allows for endpoint usage to be in the following way:

```php
$workspace_api_client = new ApiClient('workspace');

// This would list all of the groups under an organization
$workspace_api_client->directory()->groups()->list();
```

##### Product Group Class Template
```php
<?php

# TODO: FILL OUT <ProductGroup>
namespace Glamstack\GoogleWorkspace\Resources\<ProductGroup>;

use Glamstack\GoogleWorkspace\ApiClient;

class <ProductGroup> extends ApiClient
{
    # TODO: FILL OUT <Product Group URL>
    public const BASE_URL = "<Product Group URL>";
}
```

#### Adding An Endpoint Group To Product Group Class

To add an endpoint group to the product group class you simply need to add a new function with the name of the endpoint group (i.e. `Groups`). It will need to return a new initialization of the class. See [](#how-to-add-an-endpoint-group-to-product-group-class-method-template)

##### Adding An Endpoint Group To Product Group Class Method Template
```php

<?php

namespace Glamstack\GoogleWorkspace\Resources\Directory>;

use Glamstack\GoogleWorkspace\ApiClient;

class Directory extends ApiClient
{
    public const BASE_URL = "https://admin.googleapis.com/admin/directory/v1";


    /**
     * Creates a <Endpoint> object
     *
     * @return <Endpoint>
     */
    public function <Endpoint>(): <Endpoint>
    {
        # TODO: Replace `<Endpoint>` with the endpoint group name (i.e `Groups`)
        return new <Endpoint>($this, self::BASE_URL);
    }
}

```

#### Creating An Endpoint Class

The creation of an endpoint class should be done with the [Creating An Endpoint Class Template](#creating-an-endpoint-class-template)

##### Creating An Endpoint Class Template
```php
<?php

# TODO: FILL OUT <ProductGroup>
namespace Glamstack\GoogleWorkspace\Resources\<ProductGroup>;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\BaseClient;

# TODO: FILL OUT <EndpointName>
class <EndpointName> extends BaseClient
{
    private string $base_url;

    public function __construct(ApiClient $api_client, string $base_url)
    {
        parent::__construct($api_client);
        $this->base_url = $base_url;
    }
}
```

#### Updating the Endpoint Class With A New Endpoint

Creating a new endpoint for each of the classes can be done with by simply utilizing the `BaseClient` HTTP request with the file path of the endpoint and the request_data being passed in after the URL.

##### New Endpoint Example

This is the example of creating a `list` endpoint for the `Directory/Groups`
```php

<?php

namespace Glamstack\GoogleWorkspace\Resources\Directory;

use Glamstack\GoogleWorkspace\ApiClient;
use Glamstack\GoogleWorkspace\Resources\BaseClient;

class Groups extends BaseClient
{
    private string $base_url;

    public function __construct(ApiClient $api_client, string $base_url)
    {
        parent::__construct($api_client);
        $this->base_url = $base_url;


    /**
     * List all groups of a domain
     *
     * @param array $request_data
     *      Optional request data to use with list
     *
     * @return object|string
     */
    public function list($request_data){
        return BaseClient::getRequest($this->base_url . '/groups',
            $request_data->request_data
    }
```

While some methods may be more complex than this, in theory this should be about all that is needed for each endpoint.
