<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Route;

use Cake\Routing\Route\Route;

/**
 * Class RouteDecorator
 *
 * @package SwaggerBake\Lib\Decorator
 *
 * Decorates a Cake\Routing\Route\Route
 */
class RouteDecorator
{
    /**
     * @var \Cake\Routing\Route\Route
     */
    private $route;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $plugin;

    /**
     * @var string|null
     */
    private $controller;

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var string|null
     */
    private $template;

    /**
     * The controllers fully qualified namespace (e.g. \App\Controller\ActorsController)
     *
     * @var string|null
     */
    private $controllerFqn;

    /**
     * @param \Cake\Routing\Route\Route $route Route
     */
    public function __construct(Route $route)
    {
        $defaults = (array)$route->defaults;

        $methods = $defaults['_method'];
        if (!is_array($defaults['_method'])) {
            $methods = explode(', ', $defaults['_method']);
        }

        $this
            ->setTemplate($route->template)
            ->setName($route->getName())
            ->setPlugin($defaults['plugin'])
            ->setController($defaults['controller'])
            ->setAction($defaults['action'])
            ->setMethods($methods);
    }

    /**
     * @return \Cake\Routing\Route\Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param \Cake\Routing\Route\Route $route Route
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name Name of the route
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    /**
     * @param string|null $plugin Name of the plugin this route is associated with
     * @return $this
     */
    public function setPlugin(?string $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): ?string
    {
        return ucfirst($this->controller);
    }

    /**
     * @param string $controller Name of the Controller this route is associated with
     * @return $this
     */
    public function setController(string $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action The controller method
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods HTTP methods
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->methods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string $template The templated route
     * @return $this
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getControllerFqn(): ?string
    {
        return $this->controllerFqn;
    }

    /**
     * @param string $controllerFqn controller fqn
     * @return $this
     */
    public function setControllerFqn(string $controllerFqn)
    {
        $this->controllerFqn = $controllerFqn;

        return $this;
    }

    /**
     * Converts a CakePHP route template to an OpenAPI path
     *
     * @return string
     */
    public function templateToOpenApiPath(): string
    {
        $pieces = array_map(
            function ($piece) {
                if (substr($piece, 0, 1) == ':') {
                    return '{' . str_replace(':', '', $piece) . '}';
                }

                return $piece;
            },
            explode('/', $this->template)
        );

        return implode('/', $pieces);
    }
}
