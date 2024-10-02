<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use InvalidArgumentException;
use SwaggerBake\Lib\OpenApi\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiSchema
{
    /**
     *  Default behavior. Adds the schema if it matches a controller with a restful route.
     */
    public const VISIBLE_DEFAULT = 1;

    /**
     * Always add the schema.
     */
    public const VISIBLE_ALWAYS = 2;

    /**
     * Never add the schema to the default location, but adds it to vendor location. This hides the schema from the
     * Swagger UIs Schemas section, but still allows the schema to be used for request and response bodies.
     */
    public const VISIBLE_HIDDEN = 3;

    /**
     * Never add the Schema. Warning this can break request body definitions and response samples.
     */
    public const VISIBLE_NEVER = 4;

    /**
     * @param int $visibility See class constants for options [default: VISIBLE_DEFAULT].
     * @param string|null $title The title of the schema [default: null].
     * @param string|null $description The description of the schema [default: null].
     * @param string|null $name The name of the OpenAPI property [defaults to the CakePHP table alias].
     */
    public function __construct(
        public readonly int $visibility = self::VISIBLE_DEFAULT,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $name = null
    ) {
        if ($this->visibility < 1 || $this->visibility > 4) {
            throw new InvalidArgumentException(
                'OpenApiSchema visibility must be 1 through 4. See class constants'
            );
        }
    }

    /**
     * Create a Schema from this attribute instance.
     *
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    public function createSchema(): Schema
    {
        return (new Schema($this->title))
            ->setName($this->name)
            ->setVisibility($this->visibility)
            ->setDescription($this->description);
    }
}
