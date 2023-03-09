<?php declare(strict_types=1);

namespace LaravelSerializer\Framework\Routing;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use LaravelSerializer\ConfigLoader;
use LaravelSerializer\Framework\SerializerConfigLoader;
use Serializer\ArraySerializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class SerializerRouter extends Router
{
    private ArraySerializer $serializer;

    public function __construct(
        Dispatcher $events,
        Container $container,
        RouteCollectionInterface $routes,
        ConfigLoader $config,
    ) {
        parent::__construct($events, $container);

        $this->serializer = new ArraySerializer($config->encoder(), $config->decoder());
        $this->routes = $routes;
    }

    public static function from(Router $router): SerializerRouter
    {
        return new SerializerRouter(
            events: $router->events,
            container: $router->container,
            routes: $router->routes,
            config: SerializerConfigLoader::create(),
        );
    }

    /**
     * @param Request $request
     * @param mixed $response
     * @return SymfonyResponse
     * @throws Throwable
     */
    public function prepareResponse($request, $response)
    {
        return match ($this->isSerializable($response)) {
            true => parent::toResponse($request, $this->serializer->serialize($response)),
            default => parent::toResponse($request, $response),
        };
    }

    /**
     * @param mixed $response
     */
    private function isSerializable($response): bool
    {
        $classes = config('serializer');

        return is_object($response) && array_key_exists(get_class($response), $classes);
    }
}
