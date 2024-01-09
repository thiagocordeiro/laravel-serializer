<?php

namespace LaravelSerializer\Framework\Dispatcher;

use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Route;
use Serializer\ArraySerializer;
use Throwable;

class SerializerCallableDispatcher extends CallableDispatcher
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

    public function dispatch(Route $route, $callable)
    {
        $response = parent::dispatch($route, $callable);

        return $this->wrapper->respond($response);
    }
}
