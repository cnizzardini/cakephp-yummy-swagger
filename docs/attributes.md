# SwaggerBake Attributes

SwaggerBake provides some optional [Attributes](https://www.php.net/manual/en/language.attributes.overview.php) for
enhanced functionality. It is helpful to understand the [OpenApi specification](https://spec.openapis.org/oas/latest.html)
when reading this documentation. Annotations exist in the following namespaces:

- `SwaggerBake\Lib\Attribute`
- `SwaggerBake\Lib\Extension`

Just a reminder that many usage examples exist in the 
[SwaggerBake Demo](https://github.com/cnizzardini/cakephp-swagger-bake-demo) and 
[MixerApi Demo](https://github.com/mixerapi/demo).


## Table of Contents

| Attribute                                           | Usage                                               | Description                                                                                                                 | 
|-----------------------------------------------------|-----------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------|
| [OpenApiDto](#openapidto)                           | Controller Action                                   | Builds OpenAPI query params and request bodies from Data Transfer Objects                                                   |
| [OpenApiForm](#openapiform)                         | Controller Action                                   | Builds OpenAPI for application/x-www-form-urlencoded request bodies                                                         |
| [OpenApiHeader](#openapiheader)                     | Controller Action                                   | Create OpenAPI header parameters                                                                                            |
| [OpenApiOperation](#openapioperation)               | Controller Action                                   | Modifies OpenAPI operation                                                                                                  |
| [OpenApiPaginator](#openapipaginator)               | Controller Action                                   | Create OpenAPI query params from CakePHP Paginator Component                                                                |
| [OpenApiPath](#openapipath)                         | Controller                                          | Modifies OpenAPI paths                                                                                                      |
| [OpenApiPathParam](#openapipathparam)               | Controller Action                                   | Modify an existing OpenAPI path parameter                                                                                   |
| [OpenApiQueryParam](#openapiqueryparam)             | Controller Action                                   | Builds OpenAPI query param                                                                                                  |
| [OpenApiRequestBody](#openapirequestbody)           | Controller Action                                   | Modify OpenAPI request body                                                                                                 |
| [OpenApiResponse](#openapiresponse)                 | Controller Action                                   | Modify OpenAPI response                                                                                                     |
| [OpenApiSchema](#openapischema)                     | Entity, OpenApiDto class, or OpenApiResponse schema | Modifies OpenAPI schema                                                                                                     |
| [OpenApiSchemaProperty](#openapischemaproperty)     | Entity, OpenApiDto class, or OpenApiResponse schema | Modifies an OpenAPI schema property or defines OpenApiResponse schema                                                       |
| [OpenApiSearch](#openapisearch)                     | Controller Action                                   | Create OpenAPI query params from CakePHP Search plugin                                                                      |
| [OpenApiSecurity](#openapisecurity)                 | Controller Action                                   | Create/modify OpenAPI security                                                                                              |
| [~~OpenApiDtoQuery~~](#openapidtoquery)             | DTO class property                                  | Builds OpenAPI query param from Data Transfer Objects (deprecated, use OpenApiQueryParam in v2.2.5+)                        |
| [~~OpenApiDtoRequestBody~~](#openapidtorequestbody) | DTO class property                                  | Builds OpenAPI request body property from Data Transfer Objects (deprecated, use OpenApiSchemaProperty in v2.2.5+)          |

### OpenApiDto

Method level attribute for building query parameters or request bodies from a DataTransferObject. Your DTO will need to 
use the [OpenApiQueryParam](#OpenApiQueryParam) or [OpenApiSchemaProperty](#OpenApiSchemaProperty) on its properties 
depending on the request type. 

| Property   | Type / Default | OA Spec | Description                     | 
|------------|----------------|---------|---------------------------------|
| class      | string         | No      | Required. FQN of the DTO class. |

Example DTO declaration:

```php
#[OpenApiDto(class: ActorDto::class)]
public function index() {}
```

OpenApiSchemaProperty can be applied at the class or property level, example:

```php
#[OpenApiSchemaProperty(name: "a_property")]
class ActorDto
{
    public function __construct(
        #[OpenApiSchemaProperty(name: "first_name")]
        private string $firstName
    ) {
    }
}
```

When your DTO is a [CakePHPs Modelless Form](https://book.cakephp.org/5/en/core-libraries/form.html) the schema and 
validations are built automatically.

### OpenApiForm

Method level attribute for adding form data fields. See the OpenAPI documentation on
[schema types](https://spec.openapis.org/oas/v3.0.3#schema-object) for greater detail.

| Attribute          | Type / Default    | OA Spec? | Description                                                                                            |
|--------------------|-------------------|----------|--------------------------------------------------------------------------------------------------------|
| name               | string            | N        | Required. Name of the schema property                                                                  |
| type               | string `"string"` | Y        | Date type such as integer, string, array etc...                                                        |
| format             | ?string `null`    | Y        | Date format such as int32, date-time, etc...                                                           |
| title              | ?string `null`    | Y        | Title of the property                                                                                  |
| description        | ?string `null`    | Y        | Description of the property                                                                            |
| example            | mixed `null`      | Y        | An example value                                                                                       |
| isReadOnly         | bool `false`      | Y        | Is the property read only?                                                                             |
| isWriteOnly        | bool `false`      | Y        | Is the property write only?                                                                            |
| isRequired         | bool `false`      | Y        | Is the property required?                                                                              |
| default            | mixed `null`      | Y        | A default value                                                                                        |
| isNullable         | bool `false`      | Y        | Can the value be null?                                                                                 |
| isDeprecated       | bool `false`      | Y        | Is the property deprecated?                                                                            |
| multipleOf         | ?float `null`     | Y        | The value must be a multiple of this number. For example, if 5 then accepted values are 5, 10, 15 etc. |
| minimum            | ?float `null`     | Y        | The minimum allowed numeric value                                                                      |
| isExclusiveMinimum | bool `false`      | Y        | Is the `minimum` value excluded from the range.                                                        |
| maximum            | ?float `null`     | Y        | The maximum allowed numeric value                                                                      |
| isExclusiveMaximum | bool `false`      | Y        | Is the `maximum` value excluded from the range.                                                        |
| minLength          | ?integer `null`   | Y        | The minimum length of a string                                                                         |
| maxLength          | ?integer `null`   | Y        | The maximum length of a string                                                                         |
| pattern            | ?string `null`    | Y        | A regex pattern the value must follow                                                                  |
| minItems           | ?integer `null`   | Y        | The minimum items allowed in a list                                                                    |
| maxItems           | ?integer `null`   | Y        | The maximum items allowed in a list                                                                    |
| hasUniqueItems     | bool `false`      | Y        | The list must contain unique items                                                                     |
| minProperties      | ?integer `null`   | Y        | http://spec.openapis.org/oas/v3.0.3#properties                                                         |
| maxProperties      | ?integer `null`   | Y        | http://spec.openapis.org/oas/v3.0.3#properties                                                         |
| enum               | array `[]`        | Y        | An enumerated list of of options for the value                                                         |

Example:

```php
#[OpenApiForm(name: 'username', minLength: 8, maxLength: 64, pattern: '[a-zA-z]', isRequired: true)]
public function add() {}
```

OpenAPI:

```yaml
      requestBody:
        content:
          application/x-www-form-urlencoded:
            schema:
              type: object
              properties:
                username:
                  type: string
                  minLength: 8
                  maxLength: 64
                  pattern: [a-zA-Z]
                  required: true
```

### OpenApiHeader

Method level attribute for adding [header](https://spec.openapis.org/oas/latest.html#header-object) parameters.

| Property        | Type / Default            | OA Spec | Description                                                                                        | 
|-----------------|---------------------------|---------|----------------------------------------------------------------------------------------------------|
| name            | string `""`               | Y       | Name of the query parameter. Required if `ref` is not defined                                      |
| ref             | string `""`               | Y       | An OpenAPI `$ref` such as `#/components/parameters/ParameterName`. Required if name is not defined |
| type            | string `"string"`         | Y       | Date type such as integer, string, array etc...                                                    |
| format          | string `""`               | Y       | Date format such as int32, date-time, etc...                                                       |
| description     | string `""`               | Y       | Description of the parameter                                                                       |
| isRequired      | bool `false`              | Y       | Is this parameter required?                                                                        |
| enum            | array `[]`                | Y       | An enumerated list of accepted values                                                              |
| isDeprecated    | bool `false`              | Y       | Is this parameter deprecated?                                                                      |
| explode         | bool `false`              | Y       | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                                 |
| style           | string `""`               | Y       | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                                 |
| example         | string, bool, or int `""` | Y       | An example value                                                                                   |
| allowEmptyValue | bool `false`              | Y       | Are empty values allowed?                                                                          |

Example:

```php
#[OpenApiHeader(name: 'X-HEAD-ATTRIBUTE', type: 'string', description: 'example')]
#[OpenApiHeader(ref: '#/x-my-project/components/parameters/my-header']
public function index() {}
```

OpenAPI:

```yaml
     parameters:
       - name: X-HEAD-ATTRIBUTE
         in: header
         description: summary
         schema:
           type: string
       - $ref: #/x-my-project/components/parameters/my-header
```

### OpenApiOperation

Method level attribute for OpenApi Operations.

| Property     | Type / Default  | OA Spec | Description                                                                                                             | 
|--------------|-----------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| summary      | ?string `""`    | Yes     | Operation summary, set to null to prevent reading from docblock                                                         |
| description  | ?string `""`    | Yes     | Operation description, set to null to prevent reading from docblock                                                     |
| isVisible    | bool `true`     | No      | Setting this to false will prevent the operation from appearing in OpenApi output                                       |
| tagNames     | array `[]`      | Yes     | Sets tag names                                                                                                          |
| isDeprecated | bool `false`    | Yes     | Is the operation deprecated?                                                                                            |
| externalDocs | ?array `null`   | Yes     | External documentation                                                                                                  |
| sortOrder    | ?int `null`     | No      | The order the operation appears at in OpenAPI output. Defaults to the order the action appears in the controller class. |

Example:

```php
#[OpenApiOperation(
    summary: 'operation title',
    description: 'a description',
    tagNames: ['Internal API', 'External API'], 
    externalDocs: [
        'url' => 'https://github.com/cnizzardini/cakephp-swagger-bake', 
        'description' => 'Check out the documentation'
    ]
)]
public function index()
```

OpenAPI:

```yaml
    get:
      operationId: actors:index:get
      summary: operation title
      description: a description
      tags:
        - Internal API
        - External API
      externalDocs:
        url: https://github.com/cnizzardini/cakephp-swagger-bake
        description: Check out the documentation
```

A common use-case is to hide an operation from appearing in your OpenAPI, example:

```php
#[OpenApiOperation(isVisible: false)]
public function index()
```

### OpenApiPaginator

Method level attribute for adding [CakePHP Paginator](https://book.cakephp.org/5/en/controllers/components/pagination.html)
query parameters: page, limit, sort, and direction.  OpenApiPaginator only works on `index()` actions.

| Property         | Type / Default  | OA Spec | Description                                                                                  | 
|------------------|-----------------|---------|----------------------------------------------------------------------------------------------|
| sortEnum         | array `[]`      | No      | A list of fields that can be sorted by. This overrides the default `Paginate.sortableFields` |
| useSortTextInput | boolean `false` | No      | Display an input box in Swagger UI instead of a dropdown for sorting                         |

Default usage:

```php
#[OpenApiPaginator()]
public function index() {}
```

Override default sortable fields:

```php
#[OpenApiPaginator(sortEnum: ['id', 'name'])]
public function index() {}
```

OpenAPI:

```yaml
      parameters:
        - name: page
          in: query
          schema:
            type: integer
        - name: limit
          in: query
          schema:
            type: integer
        - name: sort
          in: query
          schema:
            type: string
            enum:
              - id
              - name
        - name: direction
          in: query
          schema:
            type: string
            enum:
              - asc
              - desc
```

### OpenApiPath

Class level attribute to define scalar [Path](https://spec.openapis.org/oas/latest.html#path-item-object) values.

| Property    | Type / Default | OA Spec | Description                                                                                          | 
|-------------|----------------|---------|------------------------------------------------------------------------------------------------------|
| isVisible   | boolean `true` | No      | Is the path and its operations visible in OpenAPI                                                    |
| ref         | string `null`  | Yes     | An OpenAPI ref such as `#/paths/my-path`                                                             |
| summary     | string `null`  | Yes     | Overwrites the default summary (if any)                                                              |
| description | string `null`  | Yes     | Overwrites the default description                                                                   |
| tags        | array `[]`     | Yes     | Sets the tags for all operations in the path. Tags set on individual operations will tak precedence. |

A common use-case for this is to hide a controller from appearing in your OpenApi (the default behavior). For instance,
you may have a bespoke endpoint that you don't want to publish:

```php
#[OpenApiPath(isVisible: false)]
class UsersController extends AppController
```

### OpenApiPathParam

Method level attribute for modifying path parameters. This is for modifying existing path parameters only. Path
parameters must first be defined in your routes file.

| Attribute     | Type / Default  | OA Spec | Description                 | 
|---------------|-----------------|---------|-----------------------------|
| name          | string `""`     | Yes     | Name of the query parameter |
| ref           | string `""`     | Yes     | Name of the query parameter |
| type          | string `string` | Yes     | Data type                   |
| format        | string `""`     | Yes     | Data format                 |
| description   | string `""`     | Yes     | Description of the parameter |
| example       | mixed `""`      | Yes     | An example value            |
| allowReserved | bool `false`    | Yes     | Allow reserved URI characters? |
| isRequired    | bool `false`    | Yes     | Is the parameter required?  |

Example:

```php
#[OpenApiPathParam(name: 'id', type: 'integer', format: 'int64', description: 'ID', isRequired: true)]
public function view($id) {}
```

OpenAPI:

```yaml
        parameters:
          - name: id
            required: true
            schema:
              description: ID
              type: integer
              format: int64
```

### OpenApiQueryParam

Attribute for adding query parameters which can be applied as a method attribute on controller actions and a class or property attribute on DTOs.

| Property        | Type / Default            | OA Spec | Description                                                                            | 
|-----------------|---------------------------|---------|----------------------------------------------------------------------------------------|
| name            | string                    | Yes     | Name of the query parameter. Required if `ref` is empty.                               |
| ref             | string                    | Yes     | An OpenApi $ref parameter describing the query parameter. Required if `name` is empty. |
| type            | string `string`           | Yes     | Data type. Required if `name` is empty.                                                |
| format          | string `""`               | Yes     | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                     |
| description     | string `""`               | Yes     | Description of the parameter                                                           |
| isRequired      | bool `false`              | Y       | Is this parameter required?                                                            |
| enum            | array `[]`                | Y       | An enumerated list of accepted values                                                  |
| isDeprecated    | bool `false`              | Yes     | Is this parameter deprecated?                                                          |
| explode         | bool `false`              | Yes     | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                     |
| style           | string `""`               | Yes     | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                     |
| example         | string, bool, or int `""` | Yes     | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9                                     |
| allowEmptyValue | bool `false`              | Yes     | Allow empty values?                                                                    |

Example:

```php
#[OpenApiQueryParam(name: "option", isRequired: true, enum: ["A","B","C"], description: "desc...")]
#[OpenApiQueryParam(ref: "#/x-my-project/components/parameters/my-parameter")]
public function index() {}
```

OpenAPI:

```yaml
      parameters:
        - name: option
          in: query
          schema:
            type: string
            enum:
              - A
              - B
              - C
        - $ref: #/x-my-project/components/parameters/my-parameter
```

### OpenApiRequestBody

Method level attribute for describing request body. Set ignoreCakeSchema for full control over request body.

| Attribute        | Type / Default | Description                                  | 
|------------------|----------------|----------------------------------------------|
| ref              | string `""`    | An optional OpenAPI $ref                     |
| description      | string `""`    | Description of the request body              |
| mimeTypes        | array `[]`     | An array of strings of mime types to support |
| required         | bool `true`    | Is the request body required?                |
| ignoreCakeSchema | bool `false`   | Ignore cake schema                           |

Example:

```php
#[OpenApiRequestBody(ref: '#/components/schema/Custom', ignoreCakeSchema: true, mimeTypes: ['application/json'])]
public function index() {}
```

OpenAPI:

```yaml
      requestBody:
        content:
          application/json:
            schema:
              $ref: '#/components/schema/Custom'
```

### OpenApiResponse

Method level attribute for controller actions defining
[response objects](https://spec.openapis.org/oas/latest.html#response-object) and their schema/content. The following 
order of operations is used to build the response:

1. `ref` and `schemaType` take precedence.
2. `schema`
3. `associations`
4. The schema inferred from CakePHP conventions.

| Property                      | Type / Default    | OA Spec | Description                                                                                                                                                           |
|-------------------------------|-------------------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| schemaType                    | string `"object"` | Y       | The schema response type, generally `"object"` or `"array"`                                                                                                           |
| statusCode                    | string `"200"`    | Y       | The HTTP response code                                                                                                                                                |
| ref                           | ?string `null`    | Y       | The OpenAPI schema (e.g. `"#/components/schemas/ModelName"`                                                                                                           |
| [schema](#Schema)             | ?string `null`    | Y       | An FQN describing a custom response schema. The class must have either one or more `#[OpenApiSchemaProperty]` attribute, implement `CustomSchemaInterface` or both.   |
| description                   | ?string `null`    | Y       | Description of the response                                                                                                                                           |
| mimeTypes                     | ?array `null`     | Y       | An array of mime types the response can, if null settings from swagger_bake config are used.                                                                          |
| [associations](#Associations) | ?array `null`     | N       | Adds associated tables to the response sample schema, see examples below.                                                                                             |
| schemaFormat                  | ?string `null`    | Y       | The schema format, generally only used for schemaType of string.                                                                                                      |

Defining a multiple mimeTypes and 400-409 status code range and an expected 200 response:

```php
#[OpenApiResponse(
    statusCode: '40x',
    ref: '#/components/schemas/Exception', 
    mimeTypes: ['application/xml','application/json']
)]
#[OpenApiResponse(schemaType: 'array', ref: '#/components/schemas/Actor')]
public function index(){}
```

OpenAPI:

```yaml
       '200':
         content:
           application/json:
             schema:
               type: array
               items:
                 $ref: '#/components/schemas/Actor'
       '40x':
         content:
           application/xml:
             schema:
               type: object
               items:
                 $ref: '#/components/schemas/Exception'
```

#### Schema

There may be instances where your need to further customize your responses. You can provide a custom schema two
ways. Both options first require you to provide an FQN to the class which describes your response schema:

```php
#[OpenApiResponse(schema: '\App\Dto\Response\MyCustomResponse')]
```

Using `CustomSchemaInterface`:

```php
namespace App\Dto\Response;

class MyCustomResponse implements \SwaggerBake\Lib\OpenApi\CustomSchemaInterface 
{
    /**
     * @inheritDoc
     */
    public static function getOpenApiSchema(): \SwaggerBake\Lib\OpenApi\Schema
    {
        return (new \SwaggerBake\Lib\OpenApi\Schema())  
            ->setTitle('Custom')
            ->setProperties([
                new SchemaProperty('name', 'string', null, 'Name of person', 'Paul'),
                new SchemaProperty('age', 'integer', 'int32', 'Age of person', 32)
            ]);
    }
}
```

Using `#[OpenApiSchemaProperty]`:

```php
namespace App\Dto\Response;

#[OpenApiSchemaProperty(name: 'up_here', type: 'string', description: 'yes even up here too')]
class MyCustomResponse 
{
    #[OpenApiSchemaProperty(name: 'name', type: 'string', example: 'Paul')]
    public string $name;
    #[OpenApiSchemaProperty(name: 'age', type: 'integer', format: 'int32', example: 32)]
    public int $age;
}
```

SwaggerBake will convert these into an OpenApi response schema for you. Note, you can use both the interface and
attributes in your response class. Attributes take precedence over the Schema returned from `getOpenApiSchema()`. By 
default, schema will not be added to `#/components/schemas` and so will not appear in SwaggerUI's schema list. You can 
add the `#[OpenApiSchema]` attribute to your schema class to change the default behavior.

#### Associations

The association property allows you to include associations defined in your Table class within your OpenAPI response
sample schema. To not include associations:

```php
#[OpenApiResponse(associations: false)]
```

To include all immediately associated tables (depth of one):

```php
#[OpenApiResponse(associations: [])]
```

To include deeper associations or restrict the associations, use the `whiteList` option. This supports dot notation:

```php
#[OpenApiResponse(associations: ['whiteList' => ['Films.Languages', 'City']])]
```

Remember `schemaType` defaults to `object`. We can specify `array` if we are returning many records:

```php
#[OpenApiResponse(associations: [], schemaType: 'array')]
```

Since the base table is inferred using CakePHP naming conventions we can change the base table if necessary:

```php
#[OpenApiResponse(associations: ['table' => 'OtherTable'])]
```

### OpenApiSchema

Class level attribute for modifying OpenAPI Schema.

| Property                  | Type / Default | OA Spec | Description                                                                | 
|---------------------------|----------------|---------|----------------------------------------------------------------------------|
| [visibility](#visibility) | int `1`        | No      | Determines the visibility of the schema, see OpenApiSchema class constants |
| title                     | ?string `null` | Yes     | Overwrites the default title                                               |
| description               | ?string `null` | Yes     | Overwrites the default description (if any)                                |
| name                      | ?string `null` | Yes     | The name of the OpenAPI property [defaults to the CakePHP table alias].    |

#### Visibility

You can use the constants below when defining `visibility`:

| Name                             | Value | Description                                                                                                                                                                                                                                                              |
|----------------------------------|-------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `OpenApiSchema::VISIBLE_DEFAULT` | 1     | Default behavior. Adds the schema to `#/components/schema/{SchemaName}` if it matches a controller with a RESTful route.                                                                                                                                                 |
| `OpenApiSchema::VISIBLE_ALWAYS`  | 2     | Always add the schema to `#/components/schema/{SchemaName}`.                                                                                                                                                                                                             |
| `OpenApiSchema::VISIBLE_HIDDEN`  | 3     | Never add the schema to `#/components/schema/{SchemaName}` and instead adds it to `#/x-swagger-bake/components/schema/{SchemaName}`. This hides the schema from the Swagger UIs Schemas section, but still allows the schema to be used for request and response bodies. |
| `OpenApiSchema::VISIBLE_NEVER`   | 4     | Never add the schema anywhere. Warning this can break request body definitions and response samples.                                                                                                                                                                     |

Example:

```php
#[OpenApiSchema(visbility: OpenApiSchema::VISIBLE_ALWAYS, title: 'Always visible schema', name: 'RenamedCakeTableAlias')]
class Actor extends Entity{}
```

### OpenApiSchemaProperty

Class or property level attribute for customizing Schema properties. Note that the attribute does not have to exist in 
your entity. You can add adhoc attributes as needed and optionally combine with
[Virtual Fields](https://book.cakephp.org/5/en/orm/entities.html#creating-virtual-fields).

| Attribute          | Type / Default    | OA Spec?   | Description                                                                                            | 
|--------------------|-------------------|------------|--------------------------------------------------------------------------------------------------------|
| name               | string            | N          | Required. Name of the schema property                                                                  |
| type               | string `"string"` | Y          | Date type such as integer, string, array etc...                                                        |
| format             | ?string `null`    | Y          | Date format such as int32, date-time, etc...                                                           |
| title              | ?string `null`    | Y          | Title of the property                                                                                  |
| description        | ?string `null`    | Y          | Description of the property                                                                            |
| example            | mixed `null`      | Y          | An example value                                                                                       |
| isReadOnly         | bool `false`      | Y          | Is the property read only?                                                                             |
| isWriteOnly        | bool `false`      | Y          | Is the property write only?                                                                            |
| isRequired         | bool `false`      | Y          | Is the property required?                                                                              |
| default            | mixed `null`      | Y          | A default value                                                                                        |
| isNullable         | bool `false`      | Y          | Can the value be null?                                                                                 |
| isDeprecated       | bool `false`      | Y          | Is the property deprecated?                                                                            |
| multipleOf         | ?float `null`     | Y          | The value must be a multiple of this number. For example, if 5 then accepted values are 5, 10, 15 etc. |
| minimum            | ?float `null`     | Y          | The minimum allowed numeric value                                                                      |
| isExclusiveMinimum | bool `false`      | Y          | Is the `minimum` value excluded from the range.                                                        |
| maximum            | ?float `null`     | Y          | The maximum allowed numeric value                                                                      |
| isExclusiveMaximum | bool `false`      | Y          | Is the `maximum` value excluded from the range.                                                        |
| minLength          | ?int `null`       | Y          | The minimum length of a string                                                                         |
| maxLength          | ?int `null`       | Y          | The maximum length of a string                                                                         |
| pattern            | ?string `null`    | Y          | A regex pattern the value must follow                                                                  |
| minItems           | ?int `null`       | Y          | The minimum items allowed in a list                                                                    |
| maxItems           | ?int `null`       | Y          | The maximum items allowed in a list                                                                    |
| hasUniqueItems     | bool `false`      | Y          | The list must contain unique items                                                                     |
| minProperties      | ?int `null`       | Y          | http://spec.openapis.org/oas/v3.0.3#properties                                                         |
| maxProperties      | ?int `null`       | Y          | http://spec.openapis.org/oas/v3.0.3#properties                                                         |
| enum               | array `[]`        | Y          | An enumerated list of of options for the value                                                         |
| items              | array `[]`        | Y          | For use with array schema properties.                                                                  |

```php
#[OpenApiSchemaProperty(name: 'example_one', minLength: 5, maxLength: 10)]
#[OpenApiSchemaProperty(name: 'example_two', minLength: 5, enum: ['PG','R'], isRequired: true)]
#[OpenApiSchemaProperty(name: 'example_virtual_field', isReadOnly: true)]
class Employee extends Entity {
```

OpenAPI:

```yaml
        example_one:
          type: string
          minLength: 5
          maxLength: 10
        example_two:
          type: string
          enum:
            - PG
            - R
          required: true
        example_virtual_field:
          type: string
          readOnly: true
```

### OpenApiSearch

Method level attribute for documenting search parameters using the popular
[friendsofcake/search](https://github.com/FriendsOfCake/search) plugin.

| Attribute  | Type / Default   | Description                                                                                                                                      | 
|------------|------------------|--------------------------------------------------------------------------------------------------------------------------------------------------|
| alias      | string           | Required. The table alias to be used by [TableLocator::get($alias)](https://book.cakephp.org/5/en/orm/table-objects.html#using-the-tablelocator) |
| collection | string `default` | The Cake Search collection (see [documentation](https://github.com/FriendsOfCake/search)])                                                       |
| options    | array `[]`       | Optional array to be passed into `TableLocator::get($alias, $options)`                                                                           |

```php
#[OpenApiSearch(alias: 'Films')]
public function index()
{
    $this->request->allowMethod('get');
    $query = $this->Films
        ->find('search', [
            'search' => $this->request->getQueryParams(),
            'collection' => 'default'
        ])
        ->contain(['Languages']);
    $films = $this->paginate($query);

    $this->set(compact('films'));
    $this->viewBuilder()->setOption('serialize', 'films');
}
```

Friends Of Cake Search Filter:

```php
<?php
declare(strict_types=1);

namespace App\Model\Filter;

use Search\Model\Filter\FilterCollection;

class FilmsCollection extends FilterCollection
{
    public function initialize(): void
    {
        $this
            ->add('title', 'Search.Like', [
                'before' => true,
                'after' => true,
                'mode' => 'or',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['title'],
            ])
            ->add('rating', 'Search.Value', [
                'before' => true,
                'after' => true,
                'mode' => 'or',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['rating'],
            ]);
    }
```

OpenAPI:

```yaml
      parameters:
        - name: title
          in: query
          description: Like title
          schema:
            type: string
        - name: rating
          in: query
          description: Value rating
          schema:
            type: string
```

### OpenApiSecurity
Method level attribute for documenting security requirements on an Operation. See the main documentation for more 
information. You may define multiple.

| Property   | Type / Default | OA Spec | Description                           | 
|------------|----------------|---------|---------------------------------------|
| name       | string         | Yes     | Required. Name of the security option |
| scopes     | array `[]`     | Yes     | Security Scopes                       |

Here is an example of documenting that an index endpoint requires BearerAuth security with read scope. The `name`
should match what is defined in your YAML [Security Scheme Object](https://spec.openapis.org/oas/latest.html#security-scheme-object).

```php
#[OpenApiSecurity(name: 'BearerAuth', scopes: ['read'])]
#[OpenApiSecurity(name: 'ApiKey')]
public function index(){}
```
