<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Operation\ExceptionResponse;

class ExceptionResponseTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['index','update']
            ]);
        });

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

    public function test_error_codes(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $exceptions = [
            '400' => '\Cake\Http\Exception\BadRequestException',
            '401' => '\Cake\Http\Exception\UnauthorizedException',
            '403' => '\Cake\Http\Exception\ForbiddenException',
            '404' => '\Cake\Datasource\Exception\RecordNotFoundException',
            '405' => '\Cake\Http\Exception\MethodNotAllowedException',
            '500' => '\Exception'
        ];

        $factory = DocBlockFactory::createInstance();
        foreach ($exceptions as $code => $exception) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */
            $throws = $factory->create("/** @throws $exception */ */")->getTagsByName('throws')[0];
            $exception = (new ExceptionResponse($config))->build($throws);
            $this->assertEquals($code, $exception->getCode());
        }
    }

    public function test_description(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $factory = DocBlockFactory::createInstance();
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */
        $throws = $factory->create("/** @throws \Exception description */")->getTagsByName('throws')[0];
        $exception = (new ExceptionResponse($config))->build($throws);
        $this->assertEquals('description', $exception->getDescription());
    }

    public function test_schema(): void
    {
        $this->markAsRisky();
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $factory = DocBlockFactory::createInstance();
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */
        $throws = $factory->create("/** @throws \Exception description */")->getTagsByName('throws')[0];
        $exception = (new ExceptionResponse($config))->build($throws);
        $this->assertEquals('#/components/schemas/Exception', $exception->getSchema());
    }

    public function test_exception_schema_interface(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $factory = DocBlockFactory::createInstance();
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */

        $exceptionFqn = '\SwaggerBake\Test\TestCase\Lib\Operation\OpenApiExceptionSchemaInterfaceImplementation';
        $throws = $factory
            ->create("/** @throws $exceptionFqn */")
            ->getTagsByName('throws')[0];

        $schema = (new ExceptionResponse($config))->build($throws)->getSchema();

        $this->assertEquals('MyException', $schema->getTitle());
        $this->assertCount(2, $schema->getProperties());
    }
}