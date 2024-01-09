<?php

declare(strict_types=1);

namespace LaravelSerializer\Framework\Providers;

use Illuminate\Http\Request;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\ServiceProvider;
use LaravelSerializer\Framework\Dispatcher\SerializerCallableDispatcher;
use LaravelSerializer\Framework\Dispatcher\SerializerControllerDispatcher;
use Serializer\ArraySerializer;
use Serializer\Builder\Decoder\DecoderFactory;
use Serializer\Builder\Decoder\FileLoader\PipelineDecoderFileLoader;
use Serializer\Builder\Encoder\EncoderFactory;
use Serializer\Builder\Encoder\FileLoader\PipelineEncoderFileLoader;
use Serializer\JsonSerializer;

class RequestSerializationProvider extends ServiceProvider
{
    public function boot(): void
    {
        $config = __DIR__ . '/../../config/serializer.php';
        $this->publishes([$config => config_path('serializer.php')]);
    }

    public function register(): void
    {
        $cache = storage_path('/app/serializer');
        $encoder = new EncoderFactory(PipelineEncoderFileLoader::full($cache));
        $decoder = new DecoderFactory(PipelineDecoderFileLoader::full($cache));

        $arraySerializer = new ArraySerializer($encoder, $decoder);
        $jsonSerializer = new JsonSerializer($encoder, $decoder);

        $this->app->bind(ArraySerializer::class, fn () => $arraySerializer);
        $this->app->bind(JsonSerializer::class, fn () => $jsonSerializer);
        $this->app->singleton(CallableDispatcher::class, SerializerCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, SerializerControllerDispatcher::class);

        $classes = config('serializer', []);

        foreach ($classes as $class => $setup) {
            $this->app->bind($class, function () use ($class, $arraySerializer, $jsonSerializer) {
                $request = $this->app->get(Request::class);
                $data = match ($request->getContentTypeFormat()) {
                    'json' => (string)$request->getContent(),
                    default => (object)array_merge(
                        $request->query->all(),
                        $request->request->all(),
                    ),
                };
                $serializer = match ($request->getContentTypeFormat()) {
                    'json' => $jsonSerializer,
                    default => $arraySerializer,
                };

                return $serializer->deserialize($data, $class);
            });
        }
    }
}
