<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Stores values of swagger_bake.php configuration file.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Configuration
{
    /**
     * @var string APP root, this is just for testing
     */
    protected string $root;

    /**
     * @var string The base prefix for your API, e.g. `/` or `/api/`
     */
    protected string $prefix;

    /**
     * @var string A base Swagger YML file, see example in assets (e.g. `/config/swagger.yml`).
     */
    protected string $yml;

    /**
     * @var string Web accessible file path the JSON file is written to (e.g. `/webroot/swagger.json`).
     */
    protected string $json;

    /**
     * @var string The URL browsers will use to access the JSON file (e.g. `/swagger.json`).
     */
    protected string $webPath;

    /**
     * @var string The default document type, either swagger or redoc.
     */
    protected string $docType = 'swagger';

    /**
     * @var bool Should OpenAPI be reloaded when the SwaggerBake::index route is called.
     */
    protected bool $hotReload = false;

    /**
     * @var string Default exception schema in your OpenAPI YAML file.
     */
    protected string $exceptionSchema = 'Exception';

    /**
     * @var array<string>  The requested mimetypes accepted by your API.
     */
    protected array $requestAccepts = ['application/json'];

    /**
     * @var array<string>  The mimetypes your API responds with.
     */
    protected array $responseContentTypes = ['application/json'];

    /**
     * @var int json_encode flags to be used when generation OpenAPI JSON file.
     * @link https://www.php.net/manual/en/function.json-encode.php
     */
    protected int $jsonOptions = JSON_PRETTY_PRINT;

    /**
     * @var array<string>  The HTTP methods implemented for edit() actions.
     */
    protected array $editActionMethods = ['PATCH'];

    /**
     * @var string The connection name to use when loading tables for building schemas from models.
     */
    protected string $connectionName = 'default';

    /**
     * @var array Array of namespaces. Useful if your controllers or entities exist in non-standard namespace such
     *      as a plugin. This was mostly added to aid in unit testing, but there are cases where controllers may
     *      exist in a plugin namespace etc...
     */
    protected array $namespaces = [
        'controllers' => ['\App\\'],
        'entities' => ['\App\\'],
        'tables' => ['\App\\'],
    ];

    /**
     * @param array $config SwaggerBake configurations (useful for unit tests mainly). Default: []
     * @param string $root The application ROOT (useful for unit tests mainly). Default: ROOT
     */
    public function __construct(array $config = [], string $root = ROOT)
    {
        $this->root = $root;
        try {
            $config = !empty($config) ? $config : Configure::readOrFail('SwaggerBake');
        } catch (RuntimeException $e) {
            throw new SwaggerBakeRunTimeException(
                'SwaggerBake config missing. Have you added it to your `config/bootstrap.php`? ' . $e->getMessage(),
                500,
                $e
            );
        }

        foreach (['yml', 'json', 'webPath', 'prefix'] as $property) {
            if (!array_key_exists(key: $property, array: $config)) {
                throw new InvalidArgumentException(
                    "Property `$property` must be defined in your config/swagger_bake.php configuration file."
                );
            }
        }

        foreach ($config as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new LogicException("Property $property does not exist in class " . static::class);
            }
            $setter = 'set' . ucfirst($property);
            if (!method_exists($this, $setter)) {
                throw new LogicException(
                    sprintf(
                        'Method %s does not exist in class %s but is trying to be called.',
                        $setter,
                        self::class
                    )
                );
            }
            $this->{$setter}($value);
        }
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix The base prefix for your API, e.g. `/` or `/api/`
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->throwInvalidArgExceptionIfPrefixInvalid(
            $prefix,
            "Invalid prefix: $prefix. Prefix must be a valid URI path such as `/` or `/api`."
        );

        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getYml(): string
    {
        return $this->root . $this->yml;
    }

    /**
     * @param string $yml A base Swagger YML file, see example in assets (e.g. `/config/swagger.yml`).
     * @return $this
     */
    public function setYml(string $yml)
    {
        $message = 'Generally this value should be placed in your projects webroot directory.';

        if (!str_starts_with(haystack: $yml, needle: '/')) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid yml: `%s`. Value should start with a / an be relative to your 
                    applications ROOT. $message",
                    $yml
                )
            );
        }

        $path = $this->root . $yml;
        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'A YML file is required but none was found at "%s". See "%s" for a sample file.',
                    $path,
                    '$vendor/cnizzardini/cakephp-swagger-bake/assets/swagger.yml'
                )
            );
        }

        $this->yml = $yml;

        return $this;
    }

    /**
     * @return string
     */
    public function getJson(): string
    {
        return $this->root . $this->json;
    }

    /**
     * @param string $json Web accessible file path the JSON file is written to (e.g. `/webroot/swagger.json`).
     * @return $this
     */
    public function setJson(string $json)
    {
        if (!str_starts_with(haystack: $json, needle: '/')) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid json: `%s`. Value should start with a `/` and be relative to your 
                    applications ROOT. Generally this value should be placed in your projects webroot directory.",
                    $json
                )
            );
        }

        $this->json = $json;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebPath(): string
    {
        return $this->webPath;
    }

    /**
     * @param string|null $webPath The URL browsers will use to access the JSON file (e.g. `/swagger.json`).
     * @return $this
     */
    public function setWebPath(?string $webPath)
    {
        $this->throwInvalidArgExceptionIfPrefixInvalid(
            $webPath,
            "Invalid webPath: `$webPath`. webPath must be a valid web accessible path based e.g. /swagger.json. 
            Generally if your application serves the json file from something like https://example.com/swagger.json 
            this value should be /swagger.json "
        );

        $this->webPath = $webPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocType(): string
    {
        return $this->docType;
    }

    /**
     * @param string $docType Valid types are swagger and redoc
     * @return $this
     */
    public function setDocType(string $docType)
    {
        $docType = strtolower($docType);
        $allowed = ['swagger','redoc'];
        if (!in_array($docType, $allowed)) {
            throw new InvalidArgumentException(
                "Invalid docType: $docType. Doctype must be one of " . implode(', ', $allowed)
            );
        }
        $this->docType = $docType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHotReload(): bool
    {
        return $this->hotReload;
    }

    /**
     * @param bool $hotReload Should OpenAPI be reloaded when the SwaggerBake::index route is called.
     * @return $this
     */
    public function setHotReload(bool $hotReload)
    {
        $this->hotReload = $hotReload;

        return $this;
    }

    /**
     * @return string
     */
    public function getExceptionSchema(): string
    {
        return $this->exceptionSchema;
    }

    /**
     * @param string $exceptionSchema The exception schema, for example if your default exception schema is
     *  `#/components/Schema/Exception` then the argument should be: Exception
     * @return $this
     */
    public function setExceptionSchema(string $exceptionSchema)
    {
        $this->exceptionSchema = $exceptionSchema;

        return $this;
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        foreach ($this->namespaces as $k => $ns) {
            $this->namespaces[$k] = array_unique($ns);
        }

        return $this->namespaces;
    }

    /**
     * @param array $namespaces Array of namespaces. Useful if your controllers or entities exist in non-standard
     *  namespace such as a plugin. This was mostly added to aid in unit testing, but there are cases where controllers
     *  may exist in a plugin namespace etc
     * @return $this
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;

        return $this;
    }

    /**
     * @return array
     */
    public function getParsedYml(): array
    {
        return Yaml::parseFile($this->getYml());
    }

    /**
     * @return mixed|string
     */
    public function getTitleFromYml(): mixed
    {
        $yml = $this->getParsedYml();

        return $yml['info']['title'] ?? '';
    }

    /**
     * @param string|null $doctype The layout type ("redoc" or null for Swagger). Default: null
     * @return string
     */
    public function getLayout(?string $doctype = null): string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.redoc';
        }

        return 'SwaggerBake.default';
    }

    /**
     * @param string|null $doctype The documentation type ("redoc" or null for Swagger). Default: null
     * @return string
     */
    public function getView(?string $doctype = null): string
    {
        $doctype = empty($doctype) ? $this->getDocType() : $doctype;
        if ($doctype == 'redoc') {
            return 'SwaggerBake.Swagger/redoc';
        }

        return 'SwaggerBake.Swagger/index';
    }

    /**
     * @return array
     */
    public function getRequestAccepts(): array
    {
        return $this->requestAccepts;
    }

    /**
     * @param array<string> $requestAccepts The requested mimetypes accepted by your API.
     * @return $this
     */
    public function setRequestAccepts(array $requestAccepts)
    {
        $this->requestAccepts = $requestAccepts;

        return $this;
    }

    /**
     * @return array
     */
    public function getResponseContentTypes(): array
    {
        return $this->responseContentTypes;
    }

    /**
     * @param array<string> $responseContentTypes The mimetypes your API responds with.
     * @return $this
     */
    public function setResponseContentTypes(array $responseContentTypes)
    {
        $this->responseContentTypes = $responseContentTypes;

        return $this;
    }

    /**
     * @return int
     */
    public function getJsonOptions(): int
    {
        return $this->jsonOptions;
    }

    /**
     * @param int $jsonOptions json_encode flags to be used when generation OpenAPI JSON file.
     * @link https://www.php.net/manual/en/function.json-encode.php
     * @return $this
     */
    public function setJsonOptions(int $jsonOptions)
    {
        $this->jsonOptions = $jsonOptions;

        return $this;
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * @param string $connectionName Connection name to use when loading tables for building schemas from models.
     * @return $this
     */
    public function setConnectionName(string $connectionName)
    {
        $configuredConnections = ConnectionManager::configured();

        if (!in_array($connectionName, $configuredConnections)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid connectionName supplied: %s. Must be one of %s',
                    $connectionName,
                    implode(', ', $configuredConnections)
                )
            );
        }

        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getEditActionMethods(): array
    {
        return $this->editActionMethods;
    }

    /**
     * @param array<string> $editActionMethods Valid types are POST, PUT, and PATCH.
     * @return $this
     */
    public function setEditActionMethods(array $editActionMethods)
    {
        $methods = ['POST', 'PUT', 'PATCH'];
        $results = array_filter($editActionMethods, function ($method) use ($methods) {
            return !in_array(strtoupper($method), $methods);
        });

        if (count($results)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid editActionMethod supplied: %s. Must be one of %s',
                    implode(', ', $results),
                    implode(', ', $methods)
                )
            );
        }

        $this->editActionMethods = $editActionMethods;

        return $this;
    }

    /**
     * @param string $prefix The prefix to validate
     * @param string $message The exception message
     * @return void
     */
    private function throwInvalidArgExceptionIfPrefixInvalid(string $prefix, string $message): void
    {
        if (
            !str_starts_with(haystack: $prefix, needle: '/')
            || !filter_var('https://example.com' . $prefix, FILTER_VALIDATE_URL)
        ) {
            throw new InvalidArgumentException($message);
        }
    }
}
