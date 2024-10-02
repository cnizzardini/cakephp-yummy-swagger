<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Validation\Validator;
use Exception;
use MixerApi\Core\Model\Model;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Creates an instance of SwaggerBake\Lib\OpenApi\Schema per OpenAPI specifications
 *
 * @internal
 */
class SchemaFactory
{
    private Validator $validator;

    /**
     * @var int
     */
    public const WRITEABLE_PROPERTIES = 2;

    /**
     * @var int
     */
    public const READABLE_PROPERTIES = 4;

    /**
     * @var int
     */
    public const ALL_PROPERTIES = 6;

    /**
     * Creates an instance of Schema for an ModelDecorator, returns null if the Entity is set never visible.
     *
     * @param \SwaggerBake\Lib\Model\ModelDecorator $modelDecorator ModelDecorator
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     * @throws \ReflectionException
     */
    public function create(ModelDecorator $modelDecorator, int $propertyType = 6): ?Schema
    {
        $reflection = new ReflectionClass($modelDecorator->getModel()->getEntity());
        $openApiSchema = (new AttributeFactory($reflection, OpenApiSchema::class))->createOneOrNull();

        /** @var \SwaggerBake\Lib\Attribute\OpenApiSchema $openApiSchema */
        if ($openApiSchema instanceof OpenApiSchema && $openApiSchema->visibility === OpenApiSchema::VISIBLE_NEVER) {
            return null;
        }

        $name = $openApiSchema instanceof OpenApiSchema ? $openApiSchema->name : null;

        $schema = $this
            ->createSchema($modelDecorator->getModel(), $propertyType, $name)
            ->setVisibility($openApiSchema->visibility ?? OpenApiSchema::VISIBLE_DEFAULT)
            ->setDescription($openApiSchema->description ?? '');

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Schema.created', $schema, [
                'modelDecorator' => $modelDecorator,
            ])
        );

        return $schema;
    }

    /**
     * Same as create() except this does not check SwagEntity options or dispatch an event and will always create a
     * Schema. Using create(), its possible null is returned based on SwagEntity options.
     *
     * @param \SwaggerBake\Lib\Model\ModelDecorator $modelDecorator ModelDecorator
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    public function createAlways(ModelDecorator $modelDecorator, int $propertyType = 6, ?string $name = null): Schema
    {
        return $this->createSchema($modelDecorator->getModel(), $propertyType, $name);
    }

    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param int $propertyType see public constants for options
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function createSchema(Model $model, int $propertyType = 6, ?string $name = null): Schema
    {
        $this->validator = $this->getValidator($model);

        $docBlock = $this->getDocBlock($model->getEntity());

        $properties = $this->getProperties($model, $propertyType, $docBlock);

        $schema = (new Schema())
            ->setName($name ?? (new ReflectionClass($model->getEntity()))->getShortName())
            ->setType('object')
            ->setProperties($properties);

        if (empty($schema->getDescription())) {
            $schema->setDescription($docBlock ? $docBlock->getSummary() : null);
        }

        $requiredProperties = array_filter($properties, function ($property) {
            return $property->isRequired();
        });

        if (!empty($requiredProperties)) {
            $schema->setRequired(array_keys($requiredProperties));
        }

        return $schema;
    }

    /**
     * @param \MixerApi\Core\Model\Model $model Model
     * @param int $propertyType see public constants for options
     * @param \phpDocumentor\Reflection\DocBlock|null $docBlock DocBlock instance
     * @return array
     */
    private function getProperties(Model $model, int $propertyType, ?DocBlock $docBlock): array
    {
        $return = [];
        $factory = new SchemaPropertyFactory($this->validator, $docBlock);

        foreach ($model->getProperties() as $property) {
            if (!$property->isHidden()) {
                $return[$property->getName()] = $factory->create($property);
            }
        }

        $return = array_merge($return, $this->getPropertyAnnotations($model));

        /*
         * Return all properties excluding hidden properties that are not available for writing
         */
        if ($propertyType === self::ALL_PROPERTIES) {
            return array_filter($return, function (SchemaProperty $property) {
                if (!$property->isWriteOnly() && $property->isHidden()) {
                    return false;
                }

                return true;
            });
        }

        /*
         * Return properties depending on requested property type:
         * - readable: when property is not write only and not hidden
         * - writeable: when property is not read only
         */
        return array_filter($return, function (SchemaProperty $property) use ($propertyType) {
            if ($propertyType == self::READABLE_PROPERTIES && !$property->isWriteOnly() && !$property->isHidden()) {
                return true;
            }
            if ($propertyType == self::WRITEABLE_PROPERTIES && !$property->isReadOnly()) {
                return true;
            }
        });
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface
     * @return \phpDocumentor\Reflection\DocBlock|null
     */
    private function getDocBlock(EntityInterface $entity): ?DocBlock
    {
        try {
            $reflectionClass = new ReflectionClass($entity);
        } catch (Exception $e) {
            return null;
        }

        $comments = $reflectionClass->getDocComment();

        if (!$comments) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();

        return $docFactory->create($comments);
    }

    /**
     * Returns key-value pair of property name => SchemaProperty
     *
     * @param \MixerApi\Core\Model\Model $model Model
     * @return array<\SwaggerBake\Lib\OpenApi\SchemaProperty>
     * @throws \ReflectionException
     */
    private function getPropertyAnnotations(Model $model): array
    {
        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiSchemaProperty> $attributes */
        $attributes = (new AttributeFactory(
            new ReflectionClass($model->getEntity()),
            OpenApiSchemaProperty::class
        ))->createMany();

        $return = [];
        foreach ($attributes as $attribute) {
            $return[$attribute->name] = $attribute->create();
        }

        return $return;
    }

    /**
     * Gets the Table classes Validator
     *
     * @param \MixerApi\Core\Model\Model $model Model
     * @return \Cake\Validation\Validator
     */
    private function getValidator(Model $model): Validator
    {
        try {
            $validator = $model->getTable()->validationDefault(new Validator());
        } catch (Exception $e) {
            return new Validator();
        }

        return $validator;
    }
}
