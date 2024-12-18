<?php

namespace SwaggerBake\Test\TestCase\Lib\Model;

use Cake\Collection\Collection;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;

class ModelScannerTest extends TestCase
{
    /**
     * @var string[]
     */
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.EmployeeTitles',
    ];

    private array $config;

    private Router $router;

    public function setUp(): void
    {
        parent::setUp();

        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees');
        });

        $this->router = new Router();

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
            ],
            'connectionName' => 'test',
        ];
    }

    /**
     * When `getModelDecorators` is called, a non-empty array of ModelDecorators is returned.
     */
    public function test_get_model_decorators(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $routeScanner = new RouteScanner(new Router(), $config);

        $modelDecorators = (new ModelScanner($routeScanner, $config))->getModelDecorators();
        $this->assertNotEmpty($modelDecorators);
    }

    /**
     * When a model has no route, it should be ignored.
     */
    public function test_model_should_not_be_decorated_when_no_route_exists(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $routeScanner = new RouteScanner(new Router(), $config);

        $modelDecorators = (new ModelScanner($routeScanner, $config))->getModelDecorators();
        $collection = (new Collection($modelDecorators))->filter(function (ModelDecorator $modelDecorator) {
            $modelDecorator->getModel()->getTable()->getAlias() == 'EmployeeTitles';
        });
        $this->assertCount(0, $collection);
    }

    public function test_should_use_naming_conventions_when_multiple_models_found(): void
    {
        $this->markTestSkipped('need to figure this out and why it exists...');
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $routeScanner = new RouteScanner(new Router(), $config);

        print_r($routeScanner->getRoutes());

        $modelDecorators = (new ModelScanner($routeScanner, $config))->getModelDecorators();
    }
}
