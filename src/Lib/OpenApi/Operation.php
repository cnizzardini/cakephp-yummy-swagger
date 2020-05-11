<?php

namespace SwaggerBake\Lib\OpenApi;

use InvalidArgumentException;

/**
 * Class Operation
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class Operation
{
    /** @var OperationExternalDoc|null */
    private $externalDocs;

    /** @var string  */
    private $httpMethod = '';

    /** @var string[]  */
    private $tags = [];

    /** @var string  */
    private $operationId = '';

    /** @var Parameter[]  */
    private $parameters = [];

    /** @var RequestBody|null */
    private $requestBody;

    /** @var Response[] */
    private $responses = [];

    /** @var PathSecurity[]  */
    private $security = [];

    /** @var bool  */
    private $deprecated = false;

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['httpMethod']);

        if (in_array($this->httpMethod, ['get', 'delete'])) {
            unset($vars['requestBody']);
        }
        if (empty($vars['security'])) {
            unset($vars['security']);
        }
        if (empty($vars['externalDocs'])) {
            unset($vars['externalDocs']);
        }

        return $vars;
    }

    public function hasSuccessResponseCode() : bool
    {
        $results = array_filter($this->getResponses(), function ($response) {
            return ($response->getCode() >= 200 && $response->getCode() < 300);
        });

        return count($results) > 0;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     * @return Operation
     */
    public function setHttpMethod(string $httpMethod): Operation
    {
        $type = strtolower($httpMethod);
        if (!in_array($httpMethod, ['get','put', 'post', 'patch', 'delete'])) {
            throw new InvalidArgumentException("type must be a valid HTTP METHOD, $type given");
        }

        $this->httpMethod = $httpMethod;
        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return Operation
     */
    public function setTags(array $tags): Operation
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @param string $operationId
     * @return Operation
     */
    public function setOperationId(string $operationId): Operation
    {
        $this->operationId = $operationId;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Operation
     */
    public function setParameters(array $parameters): Operation
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param Parameter $parameter
     * @return Operation
     */
    public function pushParameter(Parameter $parameter): Operation
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * @return RequestBody|null
     */
    public function getRequestBody() : ?RequestBody
    {
        return $this->requestBody;
    }

    /**
     * @param RequestBody $requestBody
     * @return Operation
     */
    public function setRequestBody(RequestBody $requestBody) : Operation
    {
        $this->requestBody = $requestBody;
        return $this;
    }

    /**
     * @return Response[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @param int $code
     * @return Response|null
     */
    public function getResponseByCode(int $code) : ?Response
    {
        return isset($this->responses[$code]) ? $this->responses[$code] : null;
    }

    /**
     * @param array $array
     * @return Operation
     */
    public function setResponses(array $array) : Operation
    {
        $this->responses = $array;
        return $this;
    }

    /**
     * @param Response $response
     * @return Operation
     */
    public function pushResponse(Response $response): Operation
    {
        $code = $response->getCode();
        $existingResponse = $this->getResponseByCode($response->getCode());
        if ($this->getResponseByCode($response->getCode())) {
            $content = $existingResponse->getContent() + $response->getContent();
            $existingResponse->setContent($content);
            $this->responses[$code] = $existingResponse;
            return $this;
        }
        $this->responses[$code] = $response;
        return $this;
    }

    /**
     * @return array
     */
    public function getSecurity(): array
    {
        return $this->security;
    }

    /**
     * @param array $security
     * @return Operation
     */
    public function setSecurity(array $security): Operation
    {
        $this->security = $security;
        return $this;
    }

    /**
     * @param PathSecurity $security
     * @return Operation
     */
    public function pushSecurity(PathSecurity $security): Operation
    {
        $this->security[] = $security;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @param bool $deprecated
     * @return Operation
     */
    public function setDeprecated(bool $deprecated): Operation
    {
        $this->deprecated = $deprecated;
        return $this;
    }

    /**
     * @return OperationExternalDoc
     */
    public function getExternalDocs() : OperationExternalDoc
    {
        return $this->externalDocs;
    }

    /**
     * @param OperationExternalDoc $externalDoc
     * @return Operation
     */
    public function setExternalDocs(OperationExternalDoc $externalDoc) : Operation
    {
        $this->externalDocs = $externalDoc;
        return $this;
    }
}