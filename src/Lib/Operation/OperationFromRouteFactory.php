<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagOperation;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DocBlockUtility;

/**
 * Class OperationFromRouteFactory
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromRouteFactory
{
    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     */
    public function __construct(Swagger $swagger)
    {
        $this->swagger = $swagger;
    }

    /**
     * Creates an instance of Operation
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param string $httpMethod Http method such i.e. PUT, POST, PATCH, GET, and DELETE
     * @param null|\SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return \SwaggerBake\Lib\OpenApi\Operation|null
     */
    public function create(RouteDecorator $route, string $httpMethod, ?Schema $schema): ?Operation
    {
        if (empty($route->getMethods())) {
            return null;
        }

        $config = $this->swagger->getConfig();
        $fqn = $route->getControllerFqn();
        if (!$fqn) {
            return null;
        }
        $controllerInstance = new $fqn();

        $docBlock = $this->getDocBlock($route);
        $annotations = AnnotationUtility::getMethodAnnotations($fqn, $route->getAction());

        if (!$this->isAllowed($route, $httpMethod, $annotations) || !$this->isVisible($annotations)) {
            return null;
        }

        $operation = (new Operation())
            ->setSummary($docBlock->getSummary())
            ->setDescription($docBlock->getDescription()->render())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName() . ':' . strtolower($httpMethod));

        $operation = $this->getOperationWithTags($operation, $route, $annotations);

        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock($operation, $docBlock);

        $operation = (new OperationPath($operation, $route, $annotations, $schema))
            ->getOperationWithPathParameters();

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $annotations);

        $operation = (new OperationSecurity($operation, $annotations, $route, $controllerInstance, $this->swagger))
            ->getOperationWithSecurity();

        $operation = (new OperationQueryParameter($operation, $annotations, $controllerInstance, $schema))
            ->getOperationWithQueryParameters();

        $operation = (new OperationRequestBody($this->swagger, $operation, $annotations, $route, $schema))
            ->getOperationWithRequestBody();

        $operation = (new OperationResponse(
            $this->swagger,
            $config,
            $operation,
            $docBlock,
            $annotations,
            $route,
            $schema
        ))->getOperationWithResponses();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Operation.created', $operation, [
                'config' => $config,
                'docBlock' => $docBlock,
                'methodAnnotations' => $annotations,
                'route' => $route,
                'schema' => $schema,
            ])
        );

        return $operation;
    }

    /**
     * Gets an instance of DocBlock from the controllers method
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @return \phpDocumentor\Reflection\DocBlock
     */
    private function getDocBlock(RouteDecorator $route): DocBlock
    {
        $emptyDocBlock = DocBlockFactory::createInstance()->create('/**  */');

        if (!class_exists($route->getControllerFqn())) {
            return $emptyDocBlock;
        }

        $fqn = $route->getControllerFqn();

        try {
            return DocBlockUtility::getMethodDocBlock(new $fqn(), $route->getAction()) ?? $emptyDocBlock;
        } catch (Exception $e) {
            return $emptyDocBlock;
        }
    }

    /**
     * Is the Operation visible in Swagger UI / OpenAPI
     *
     * @param array $annotations An array of annotation objects
     * @return bool
     */
    private function isVisible(array $annotations): bool
    {
        $swagOperations = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagOperation;
        });

        if (empty($swagOperations)) {
            return true;
        }

        $swagOperation = reset($swagOperations);

        return $swagOperation->isVisible === false ? false : true;
    }

    /**
     * Is the route, http method, and annotation combination allowed? This primarily prevents HTTP PUT methods on
     * controller `edit()` actions from appearing in OpenAPI schema by default.
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route instance of RouteDecorator
     * @param string $httpMethod http method (PUT, POST, PATCH etc..)
     * @param array $annotations an array of annotation objects
     * @return bool
     */
    private function isAllowed(RouteDecorator $route, string $httpMethod, array $annotations): bool
    {
        if (strtoupper($httpMethod) !== 'PUT' || $route->getAction() !== 'edit') {
            return true;
        }

        $swagOperations = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagOperation;
        });

        if (empty($swagOperations)) {
            return false;
        }

        $swagOperation = reset($swagOperations);

        return $swagOperation->showPut;
    }

    /**
     * Applies Operation::tags to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param array $annotations An array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation ]
     */
    private function getOperationWithTags(Operation $operation, RouteDecorator $route, array $annotations): Operation
    {
        $swagOperations = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagOperation;
        });

        $swagOperation = reset($swagOperations);

        if (empty($swagOperation->tagNames)) {
            return $operation->setTags([
                Inflector::humanize(Inflector::underscore($route->getController())),
            ]);
        }

        return $operation->setTags($swagOperation->tagNames);
    }
}
