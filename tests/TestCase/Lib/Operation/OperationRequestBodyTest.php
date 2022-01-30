<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiForm;
use SwaggerBake\Lib\Attribute\OpenApiRequestBody;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationRequestBody;
use SwaggerBake\Lib\Swagger;
use SwaggerBakeTest\App\Dto\EmployeeDataRequest;
use SwaggerBakeTest\App\Dto\EmployeeDataRequestConstructorPromotion;

class OperationRequestBodyTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private Router $router;

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function test_openapi_form(): void
    {
        $this->__setUp();
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([

                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiForm::class, [
                            'name' => 'test',
                            'type' => 'string',
                            'isRequired' => false,
                        ])
                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ]),
                )
            );

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            new Operation('hello', 'post'),
            $route,
            $mockReflectionMethod
        );

        $operation = $operationRequestBody->getOperationWithRequestBody();

        $requestBody = $operation->getRequestBody();
        $content = $requestBody->getContentByType('application/x-www-form-urlencoded');

        $schema = $content->getSchema();
        $this->assertEquals('object', $schema->getType());

        $properties = $schema->getProperties();
        $this->assertArrayHasKey('test', $properties);;
    }

    public function test_openapi_dto(): void
    {
        $this->__setUp();
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        foreach ([EmployeeDataRequest::class, EmployeeDataRequestConstructorPromotion::class] as $class) {
            $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
            $mockReflectionMethod->expects($this->any())
                ->method(
                    'getAttributes'
                )
                ->will(
                    $this->onConsecutiveCalls(
                        $this->returnValue([

                        ]),
                        $this->returnValue([

                        ]),
                        $this->returnValue([
                            new ReflectionAttribute(OpenApiDto::class, [
                                'class' => $class,
                            ])
                        ]),
                        $this->returnValue([

                        ]),
                    )
                );

            $operationRequestBody = new OperationRequestBody(
                $swagger,
                new Operation('hello', 'post'),
                $route,
                $mockReflectionMethod
            );

            $operation = $operationRequestBody->getOperationWithRequestBody();

            $requestBody = $operation->getRequestBody();
            $content = $requestBody->getContentByType('application/x-www-form-urlencoded');

            $schema = $content->getSchema();
            $this->assertEquals('object', $schema->getType());

            $properties = $schema->getProperties();
            $this->assertArrayHasKey('last_name', $properties, "failed for $class");
            $this->assertArrayHasKey('first_name', $properties, "failed for $class");
        }
    }

    public function test_ignore_schema(): void
    {
        $this->__setUp();
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiRequestBody::class, [
                            'ignoreCakeSchema' => true,
                        ])
                    ]),
                )
            );

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            new Operation('hello', 'post'),
            $route,
            $mockReflectionMethod,
            $swagger->getArray()['components']['schemas']['Employee']
        );

        $operation = $operationRequestBody->getOperationWithRequestBody();
        $this->assertEmpty($operation->getRequestBody());
    }

    public function test_ref(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create']
            ]);
        });
        $this->router = $router;

        $this->config = [
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
        ];

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiRequestBody::class, [
                            'ref' => '#/components/schema/Pet',
                        ])
                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ]),
                )
            );

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            new Operation('hello', 'post'),
            $route,
            $mockReflectionMethod,
            $swagger->getArray()['components']['schemas']['Pet']
        );

        $operation = $operationRequestBody->getOperationWithRequestBody();
        $content = $operation->getRequestBody()->getContentByType('application/x-www-form-urlencoded');

        $this->assertInstanceOf(Schema::class, $content->getSchema());
        $this->assertEquals('#/components/schemas/Pet', $content->getSchema()->getRefPath());
    }

    public function test_crud_and_annotation(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create']
            ]);
        });
        $this->router = $router;

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
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
        ];

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([]),
                    $this->returnValue([]),
                    $this->returnValue([]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiRequestBody::class, [
                            'description' => $desc = 'test',
                        ])
                    ]),
                )
            );

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            new Operation('hello', 'post'),
            $route,
            $mockReflectionMethod,
            $swagger->getArray()['components']['schemas']['Employee']
        );

        $operation = $operationRequestBody->getOperationWithRequestBody();
        $requestBody = $operation->getRequestBody();

        $this->assertEquals($desc, $requestBody->getDescription());
        $this->assertTrue($requestBody->isRequired());
    }

    private function __setUp(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create']
            ]);
        });
        $this->router = $router;

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
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
        ];
    }
}