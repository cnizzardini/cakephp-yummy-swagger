<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiPaginator;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class OperationQueryParameter
{
    /**
     * Adds query parameters to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation instance of the Operation
     * @param \Cake\Controller\Controller $controller instance of the Controller
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema instance of Schema or null
     * @param \ReflectionMethod|null $refMethod ReflectionMethod of the Controller->action or null
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function __construct(
        private readonly Operation $operation,
        private readonly Controller $controller,
        private readonly ?Schema $schema = null,
        private readonly ?ReflectionMethod $refMethod = null,
    ) {
    }

    /**
     * Adds query parameters to the Operation
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    public function getOperationWithQueryParameters(): Operation
    {
        if ($this->operation->getHttpMethod() != 'GET') {
            return $this->operation;
        }

        if (!$this->refMethod instanceof ReflectionMethod) {
            return $this->operation;
        }

        $this->definePagination();
        $this->defineQueryParams();
        $this->defineDataTransferObject();

        return $this->operation;
    }

    /**
     * Adds CakePHP Paginator query parameters to the Operation
     *
     * @return void
     * @throws \ReflectionException
     */
    private function definePagination(): void
    {
        $paginator = (new AttributeFactory($this->refMethod, OpenApiPaginator::class))->createOneOrNull();
        if (!$paginator instanceof OpenApiPaginator) {
            return;
        }

        $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorPage');
        $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorLimit');
        $this->pushSortParameter($paginator);
        $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorOrder');
        $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorDirection');
    }

    /**
     * Pushes the sort parameter into the Operation based on OpenApiPaginator attributes
     *
     * @param \SwaggerBake\Lib\Attribute\OpenApiPaginator $paginator OpenApiPaginator
     * @return void
     */
    private function pushSortParameter(OpenApiPaginator $paginator): void
    {
        if ($paginator->useSortTextInput === true) {
            $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorSort');

            return;
        }

        $parameter = new Parameter(
            in: 'query',
            name: 'sort',
            schema: (new Schema())->setType('string'),
        );

        if (!empty($paginator->sortEnum)) {
            $schema = $parameter->getSchema()->setEnum($paginator->sortEnum);
            $this->operation->pushParameter($parameter->setSchema($schema));

            return;
        }

        try {
            $refClass = new ReflectionClass($this->controller);
            $paginateProperty = $refClass->getProperty('paginate');
            $paginate = $paginateProperty->getValue($this->controller);
            if (isset($paginate['sortableFields']) && is_array($paginate['sortableFields'])) {
                $schema = $parameter->getSchema()->setEnum($paginate['sortableFields']);
                $this->operation->pushParameter($parameter->setSchema($schema));

                return;
            }
        } catch (ReflectionException) {
        }

        if ($this->schema != null && is_array($this->schema->getProperties())) {
            $enumList = [];
            foreach ($this->schema->getProperties() as $property) {
                $enumList[] = $property->getName();
            }
            $schema = $parameter->getSchema()->setEnum($enumList);
            $this->operation->pushParameter($parameter->setSchema($schema));

            return;
        }

        $this->operation->pushRefParameter('#/x-swagger-bake/components/parameters/paginatorSort');
    }

    /**
     * Adds query parameters from SwagPaginator to the Operation
     *
     * @return void
     * @throws \ReflectionException
     */
    private function defineQueryParams(): void
    {
        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiQueryParam> $params */
        $params = (new AttributeFactory($this->refMethod, OpenApiQueryParam::class))->createMany();
        if (!count($params)) {
            return;
        }

        foreach ($params as $attribute) {
            $this->operation->pushParameter($attribute->createParameter());
        }
    }

    /**
     * Adds query parameters from SwagDto to the Operation
     *
     * @return void
     * @throws \ReflectionException
     */
    private function defineDataTransferObject(): void
    {
        $dto = (new AttributeFactory($this->refMethod, OpenApiDto::class))->createOneOrNull();
        if (!$dto instanceof OpenApiDto) {
            return;
        }

        if (!class_exists($dto->class)) {
            throw new SwaggerBakeRunTimeException(
                sprintf('DTO class %s not found', $dto->class)
            );
        }

        // get openapi schema query params defined on the class
        $queryParams = (new AttributeFactory(
            new ReflectionClass($dto->class),
            OpenApiQueryParam::class
        ))->createMany();
        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiQueryParam> $queryParams */
        $parameters = array_map(function ($param) {
            return $param->createParameter();
        }, $queryParams);

        // get openapi query params defined per class property
        $parameters = array_merge($parameters, (new DtoParser($dto->class))->getParameters());
        foreach ($parameters as $parameter) {
            $this->operation->pushParameter($parameter);
        }
    }
}
