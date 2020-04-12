# SwaggerBake plugin for CakePHP4

`Note: This is an alpha stage plugin and prone to lots of changes right now`

A delightfully tasty tool for generating Swagger documentation with OpenApi 3.0.0 schema. This plugin automatically 
builds swagger JSON for you with minimal configuration and effort. It operates on convention and assumes your 
application is [RESTful](https://book.cakephp.org/4/en/development/rest.html). Swagger UI 3.25.0 comes pre-installed 
with this plugin.

## Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```
composer require cnizzardini/cakephp-swagger-bake
```

Add the plugin to `Application.php`:

```php
$this->addPlugin('SwaggerBake');
```

## Basic Usage

Get going in just four easy steps:

- Create a base swagger.yml file in `config\swagger.yml`. An example file is provided [here](assets/swagger.yml). 

- Create a `config/swagger_bake.php` file. See the example file [here](assets/swagger_bake.php) for further 
explanation.


- Create a route for SwaggerBake in `config/routes.php`

```php
$routes->scope('/api', function (RouteBuilder $builder) {
    $builder->setExtensions(['json']);
    // $builder->resources() here
    $builder->connect('/', ['controller' => 'Swagger', 'action' => 'index', 'plugin' => 'SwaggerBake']);
});
```

- Use the `swagger bake` command to generate your swagger documentation. 

```sh
bin/cake swagger bake
```

Using the above example you should now see your swagger documentation after browsing to http://your-project/api

### Hot Reload Swagger JSON

You can enable hot reloading. This setting re-generates swagger.json on each reload of Swagger UI. Simply set 
`hotReload` equal to `true` in your `config/swagger_bake.php` file. This is not recommended for production.

## Annotations and Doc Block

SwaggerBake will parse some of your doc blocks for information. The first line of Doc Blocks above Controller Actions 
are used for the Path Summary. 

```php
/**
 * This will appear in the path summary
 * 
 * This line will not appear in the path summary
 */
public function index() {

}
```

SwaggerBake provides some optional Annotations for additional functionality.

#### `@SwagPaginator`
Use @SwagPaginator on Controller actions using 
[CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html). This will add the following 
query params to Swagger:
- page
- limit
- sort
- direction

```php
use SwaggerBake\Lib\Annotation\SwagPaginator;

/**
 * @SwagPaginator
 */
public function index() {
    $employees = $this->paginate($this->Employees);
    $this->set(compact('employees'));
    $this->viewBuilder()->setOption('serialize', ['employees']);
}
```

#### `@SwagQuery`
Add custom query parameters with @SwagQuery

```php
use SwaggerBake\Lib\Annotation\SwagQuery;

/**
 * @SwagQuery(name="queryParamName", type="string", required=false)
 */
public function index() {

}
```

### Extensibility

There are several options to extend the functionality of SwaggerBake

#### Using Your Own SwaggerUI

You may use your own swagger install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in step 3 of Basic Usage. In this case just reference the generated swagger.json with your own 
Swagger UI install.

#### Generate Swagger On Your Terms

There a three options for generating swagger.json:

1. Integrate `swagger bake` into your build process.

2. Enable the `hotReload` option in config/swagger_bake.php.

3. Call Swagger programmatically: 

```php
$swagger = (new \SwaggerBake\Lib\Factory\SwaggerFactory())->create();
$swagger->toArray(); # returns swagger array
$swagger->toString(); # returns swagger json
$swagger->writeFile('/full/path/to/your/swagger.json'); # writes swagger.json
```

## Console Commands

In addition to `swagger bake` these console helpers provide insight into how your Swagger documentation is generated.

#### `swagger routes` 
Generates a list of routes that will be added to your swagger documentation.

```sh
bin/cake swagger routes
```

#### `swagger models` 
Generates a list of models that will be added to your swagger documentation.

```sh
bin/cake swagger models
```

## Details

- Swagger uses your existing swagger.yml as a base for adding additional paths and schema.
- Generates JSON based on the OpenAPI 3 specification. I am still working on implementing the full spec.
- All Schemas and Paths generated must have the following in your CakePHP Application:
  - App\Model\Entity class
  - App\Controller class
  - Must be a valid route
  - Entity attributes must not be marked as hidden to be included
- SwaggerBake has been developed for application/json and has not been tested with application/xml.

## Supported Versions

This is built for CakePHP 4.x only.

| Version  | Supported | Unit Tests | Notes |
| ------------- | ------------- | ------------- | ------------- |
| 4.0 | Yes  | Yes |  |

## Common Issues

### Swagger UI 

`No API definition provided.`

Verify that swagger.json exists.

### SwaggerBakeRunTimeExceptions 

`Unable to create swagger file. Try creating an empty file first or checking permissions`

Create the swagger.json manually matching the path in your `config/swagger_bake.php` file.

`Output file is not writable`

Change permissions on your `swagger.json file`, `764` should do.

`Controller not found`

Make sure a controller actually exists for the route resource. 

### Other Issues

#### Not all of my actions (paths) are showing in Swagger

By default Cake RESTful resources will only create routes for index, view, add, edit and delete. You can add your own 
paths. Here is an example for adding a route for Employees::salutation.

```php
$builder->resources(
    'Employees',
    [
        'map' => [
            'salutation' => [
                'action' => 'salutation',
                'method' => 'GET',
                'path' => ':id/salutation'
            ]
        ]
    ]
);
```

Read the cake documentation on 
[Mapping additional resource routes](https://book.cakephp.org/4/en/development/routing.html#mapping-additional-resource-routes). 
You can also remove default routes, 
read [Limiting the routes created](https://book.cakephp.org/4/en/development/routing.html#limiting-the-routes-created).

## Reporting Issues

This is a new library so please take some steps before reporting issues. You can copy & paste the JSON SwaggerBake 
outputs into https://editor.swagger.io/ which will automatically convert the JSON into YML and display potential 
schema issues.

Please included the following in your issues a long with a brief description:

- Steps to Reproduce
- Actual Outcome
- Expected Outcome

Feature requests are welcomed.

## Contribute

Send pull requests to help improve this library. You can include SwaggerBake in your primary Cake project as a 
local source to make developing easier:

- Make a clone of this repository

- Remove `cnizzardini\cakephp-swagger-bake` from your `composer.json`

- Add a paths repository to your `composer.json`
```
"repositories": [
    {
        "type": "path",
        "url": "/absolute/local-path-to/cakephp-swagger-bake",
        "options": {
          "symlink": true
        }
    }
]
```
- Run `composer require cnizzardini/cakephp-swagger-bake @dev`

Undo these steps when you're done. Read the full composer documentation on loading from path here: 
[https://getcomposer.org/doc/05-repositories.md#path](https://getcomposer.org/doc/05-repositories.md#path)

## Unit Tests

```sh
vendor/bin/phpunit
```