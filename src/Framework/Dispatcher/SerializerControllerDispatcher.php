<?php

namespace LaravelSerializer\Framework\Dispatcher;

use Illuminate\Container\Container;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Serializer\ArraySerializer;
use Throwable;

class SerializerControllerDispatcher extends ControllerDispatcher
{
    private SerializeResponseWrapper $wrapper;

    /**
     * @throws Throwable
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->wrapper = $container->get(SerializeResponseWrapper::class);
    }

    public function dispatch(Route $route, $controller, $method)
    {
        $response = parent::dispatch($route, $controller, $method);

        return $this->wrapper->respond($response);
    }
}
