<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

use JsonSerializable;

/**
 * Class Response
 *
 * @package SwaggerBake\Lib\OpenApi
 * @see https://spec.openapis.org/oas/latest.html#response-object
 */
class Response implements JsonSerializable
{
    /**
     * @param string $code HTTP status code
     * @param string|null $description An optional description
     * @param \SwaggerBake\Lib\OpenApi\Content[] $content Array of OpenApi Content
     */
    public function __construct(
        private string $code,
        private ?string $description = null,
        private array $content = []
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['code']);
        if (empty($vars['content'])) {
            unset($vars['content']);
        }

        return $vars;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string|int $code Http status code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = (string)$code;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description Description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Content[]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param string $mimeType Mime type i.e. application/json, application/xml
     * @return \SwaggerBake\Lib\OpenApi\Content|null
     */
    public function getContentByMimeType(string $mimeType): ?Content
    {
        return $this->content[$mimeType] ?? null;
    }

    /**
     * Sets the array of Content[]
     *
     * @param \SwaggerBake\Lib\OpenApi\Content[] $contents Content
     * @return $this
     */
    public function setContent(array $contents)
    {
        $this->content = $contents;

        return $this;
    }

    /**
     * Appends to array of Content[]
     *
     * @param \SwaggerBake\Lib\OpenApi\Content $content Content
     * @return $this
     */
    public function pushContent(Content $content)
    {
        $this->content[$content->getMimeType()] = $content;

        return $this;
    }
}
