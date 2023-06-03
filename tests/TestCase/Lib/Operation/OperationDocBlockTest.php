<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Http\Exception\InternalErrorException;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\Operation\OperationDocBlock;

class OperationDocBlockTest extends TestCase
{
    /**
     * @var string[]
     */
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private array $config;

    private Operation $operation;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->operation = (new Operation('hello', 'get'));
    }

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create']
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

    /**
     * @dataProvider dataProviderForExternalDocs
     * @throws InternalErrorException
     */
    public function test_external_documentation_tags(string $tag): void
    {

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $block = <<<EOT
/** 
 * @$tag https://www.cakephp.org CakePHP
 */
EOT;
        $docBlock = DocBlockFactory::createInstance()->create($block);
        $operation = (new OperationDocBlock($config, $this->operation, $docBlock))->getOperation();
        $doc = $operation->getExternalDocs();

        $this->assertInstanceOf(OperationExternalDoc::class, $doc);
        $this->assertEquals('CakePHP', $doc->getDescription());
        $this->assertEquals('https://www.cakephp.org', $doc->getUrl());
    }

    /**
     * @dataProvider dataProviderForExternalDocs
     * @throws InternalErrorException|\ReflectionException
     */
    public function test_external_documentation_tags_without_description(string $tag): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $block = <<<EOT
/** 
* @$tag https://www.cakephp.org
*/
EOT;
        $docBlock = DocBlockFactory::createInstance()->create($block);
        $operation = (new OperationDocBlock($config, $this->operation, $docBlock))->getOperation();
        $doc = $operation->getExternalDocs();

        $this->assertInstanceOf(OperationExternalDoc::class, $doc);
        $this->assertEquals('', $doc->getDescription());
        $this->assertEquals('https://www.cakephp.org', $doc->getUrl());
    }

    /**
     * @dataProvider dataProviderForExternalDocs
     * @throws \ReflectionException
     */
    public function test_external_documentation_not_set_when_url_invalid(string $tag): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $block = <<<EOT
/** 
 * @$tag htt:/www.cakephp.org 
 */
EOT;
        $docBlock = DocBlockFactory::createInstance()->create($block);
        $operation = (new OperationDocBlock($config, $this->operation, $docBlock))->getOperation();
        
        $this->assertNull($operation->getExternalDocs());
    }

    /**
     * @throws InternalErrorException
     */
    public function test_link_tag_takes_precedence_over_see_tag(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $block = <<<EOT
/** 
 * @see https://google.com nope
 * @link https://duckduckgo.com yep
 */
EOT;
        $docBlock = DocBlockFactory::createInstance()->create($block);
        $operation = (new OperationDocBlock($config, $this->operation, $docBlock))->getOperation();
        $doc = $operation->getExternalDocs();

        $this->assertInstanceOf(OperationExternalDoc::class, $doc);
        $this->assertEquals('yep', $doc->getDescription());
        $this->assertEquals('https://duckduckgo.com', $doc->getUrl());
    }

    /**
     * @throws InternalErrorException
     */
    public function test_deprecated_tag(): void
    {

        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $block = <<<EOT
/** 
 * @deprecated 
 */
EOT;
            $docBlock = DocBlockFactory::createInstance()->create($block);
            $operation = (new OperationDocBlock($config, $this->operation, $docBlock))->getOperation();
            $this->assertTrue($operation->isDeprecated());

    }

    public function dataProviderForExternalDocs(): array
    {
        return [
            ['see'],
            ['link'],
        ];
    }
}