# MageTitans 2015, Manchester
### Magento 2 Developer Mini-Workshop

#### 6. November 2015

### Overview

##### The MageTitans_ProductStatus Module

* CLI command to show product status for SKU matches
* CLI command to disable and enable products
* REST API exposing the same functionality, accessible only by admins
  
##### Aspects of Magento 2 development touched on

* Dependency Injection (DI)
* Unit Tests
* Design by Contract (PHP Interfaces)
* Basic Module Structure
* API Service Contracts
* Repository usage
* Web-API and ACL
* Token based REST API authentication and access
* Code Generation (Proxy)

##### NOT covered

* Plugins
* Routing and Actions
* Layout
* Virtual Types
* Repository implementation
* Data API
* Events
* JS Framework
* Static-, Integration-, Functional-, Performance- and JS-Tests 
* Magento 2 Core Modules...
* So much more...

### Coding

#### Create Skeleton Module

* Create `app/code/MageTitans/ProductStatus/etc/module.xml`
* Create `app/code/MageTitans/ProductStatus/registration.php`
* Run `bin/magento module:status` -> present but disabled
* Run `bin/magento module:enable MageTitans_ProductStatus`
* Run `bin/magento setup:upgrade`
* Run `bin/magento module:status` -> present and enabled

#### Create ShowProductStatusCommand

* Create `app/code/MageTitans/ProductStatus/Test/Unit/Console/Command/ShowProductStatusCommandTest.php`
* Test the class `ShowProductStatusCommand` exists
* In the Magento base directory, run `vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MageTitans/ProductStatus/Test/Unit`
* Create `app/code/MageTitans/ProductStatus/Console/Command/ShowProductStatusCommand.php`
* Test the class is a `Symfony\Component\Console\Command\Command`
* Test it has a name `catalog:product:status`
* Test it has a description
* Test it takes a required SKU argument
* Test it displays exceptions as error messages
* Test it displays a message if there are no matches
* Test it displays a message if no products matched the given SKU
* Test it displays the status for all returned products

#### Create ProductStatusAdapterInterface

* Create `app/code/MageTitans/ProductStatus/Model/ProductStatusAdapterInterface.php`

#### Refactor test and command to use interface

* Import new interface, remove stub method declaration
* Add ENABLED and DISABLED constants to interface and use them in the test instead of hardcoded status strings

#### Create ProductStatusAdapter

* Create `app/code/MageTitans/ProductStatus/Test/Unit/Model/ProductStatusAdapterTest.php`
* Test the class exists
* Test the class implements `ProductStatusAdapterInterface`
* Test it throws an exception in SKU is not a string or empty
* Test it queries a product repository
* Test it returns an empty array if there is no match
* Test it translates the product repository search results into status array
* Test it adds the SKU as search criteria

#### Create DI configuration

* Create `app/code/MageTitans/ProductStatus/etc/di.xml`
* Add preference for `ProductStatusAdapterInterface` to point to `ProductStatusAdapter\Proxy`
* [Integration Test]: Create `MageTitans\ProductStatus\Test\Integration\DiTest`
* [Integration Test]: Test the show product status command is registered
* Add `catalogProductStatus` command to `CommandList` arguments in `etc/di.xml`
* Flush the config cache and run `bin/magento list catalog` and check the new command is listed

#### Create DisableProductCommand

* Create `app/code/MageTitans/ProductStatus/Test/Unit/Console/Command/DisableProductCommandTest.php`
* Test the class `DisableProductCommand` exists
* Test it is a `Symfony\Component\Console\Command\Command`
* Test it has the right name
* Test it has a description
* Test it takes a required SKU argument
* Test it delegates to the product status adapter
* Test it displays exceptions as error messages
* Test it displays a confirmation message

#### Implement ProductStatusAdapterInterface::disableProductWithSku

* Add a stub implementation to the class
* Make existing tests pass
* Test it throws an exception if the SKU is not a string or empty
* Test it throws an exception if the product already is disabled
* Test it disables an existing product
* Test it converts EntityNotFoundException to ProductStatusCommandExceptions for disable

#### Add DI configuration for new command

* [Integration Test]: Test the disable product command is registered
* Add `catalogProductDisable` command to `CommandList` arguments in `etc/di.xml`
* Flush the config cache and run `bin/magento catalog` and check the new command is listed
* If still needed, workaround the issue "*Area code not set: Area code must be set before starting a session.*" by injecting `Magento\Framework\App\State` and setting the area code `adminhtml` in `ProductStatusAdapter::__construct()`. Wrap it in a try/catch block since that will be required when the class is used in the API context.

#### Implement EnableProductCommand

* Almost the same steps are required as for the disable product command

#### Implement ProductStatusAdapterInterface::enableProductWithSku

* Again, this is very similar to implmenting the method disableProductWithSku

#### Create API resource ProductStatusManagement

* Create `app/code/MageTitans/ProductStatus/Api/ProductStatusManagementInterface.php`
* Add one public method `get($sku)` returning a string
* Test the class `MageTitans/ProductStatus/Model/ProductStatusManagement` exists
* Test it implements the interface
* Test it delegates to a new method of the product status adapter `getStatusBySku()`

#### Implement ProductStatusAdapterInterface::getStatusBySku

* Add a stub implementation to `ProductStatusAdapter`
* Make existing tests pass
* Test it throws an exception if the SKU is not a string or is empty
* Test it returns the products status string

#### Add configuration for Web-API

* Add preference for `\MageTitans\ProductStatus\Api\ProductStatusManagementInterface`
* Add `app/code/MageTitans/ProductStatus/etc/acl.xml`
* Add a new resource admin -> catalog -> catalog_inventory -> product_status to the ACL
* Add `app/code/MageTitans/ProductStatus/etc/webapi.xml`
* Add a `GET` route to `/V1/magetitans/product/status/:sku` mapping to the `ProductStatusManagementInterface::get` method

#### Test REST API with curl CLI

* Get a token

```bash
curl -X POST "http://magetitans2015.dev/rest/V1/integration/admin/token" \
  -H "content-type:application/json" \
  -d '{"username":"admin", "password":"<PASSWORD>"}'
```
* Send a request to the new resource

```bash
curl -X GET "http://magetitans2015.dev/rest/V1/magetitans/product/status/<SKU>" \
    -H "content-type:application/json" \
    -H "Authorization: Bearer <TOKEN>"
```

#### Implement methods for REST PUT resource to enable and disable products

By following steps similar to the examples above, implement a CLI command to enable and disable products.

#### Add PUT route for product status REST resource

Add a new REST API resource to set the status of products.

Example call to the REST resource to enable a product.

```bash
curl -X PUT "http://magetitans2015.dev/rest/V1/magetitans/product/status/<SKU>>" \
    -H "content-type:application/json" \
    -H "Authorization: Bearer <TOKEN>>" \
    -d'{"status":"enabled"}'
```
