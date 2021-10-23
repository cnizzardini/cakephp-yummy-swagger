<?php

namespace SwaggerBake\Test\TestCase\Lib\Attribute;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class OpenApiResponseTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private Swagger $swagger;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'map' => [
                    'customResponseSchema' => [
                        'action' => 'customResponseSchema',
                        'method' => 'GET',
                        'path' => 'custom-response-schema'
                    ],
                    'schemaItems' => [
                        'action' => 'schemaItems',
                        'method' => 'GET',
                        'path' => 'schema-items'
                    ],
                ]
            ]);
        });

        $config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-with-existing.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ], SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($router, $config);
        $this->swagger = new Swagger(new ModelScanner($cakeRoute, $config));
    }

    public function test_response(): void
    {
        $arr = json_decode($this->swagger->toString(), true);

        $operation = $arr['paths']['/employees/custom-response-schema']['get'];

        $schema = $operation['responses']['200']['content']['application/json']['schema'];
        $this->assertEquals('#/components/schemas/Pet', $schema['$ref']);

        $this->assertArrayHasKey('404', $operation['responses']);
        $this->assertEquals('new statusCode', $operation['responses']['404']['description']);

        $this->assertArrayHasKey('5XX', $operation['responses']);
        $this->assertEquals('status code range', $operation['responses']['5XX']['description']);
    }
}