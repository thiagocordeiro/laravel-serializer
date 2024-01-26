<?php

declare(strict_types=1);

namespace LaravelSerializer\Framework\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Contracts\CallableDispatcher;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\ServiceProvider;
use JsonException;
use LaravelSerializer\Decoder\CarbonDecoder;
use LaravelSerializer\Decoder\CarbonImmutableDecoder;
use LaravelSerializer\Encoder\CarbonEncoder;
use LaravelSerializer\Encoder\CarbonImmutableEncoder;
use LaravelSerializer\Framework\Dispatcher\SerializerCallableDispatcher;
use LaravelSerializer\Framework\Dispatcher\SerializerControllerDispatcher;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Serializer\ArraySerializer;
use Serializer\Builder\Decoder\DecoderFactory;
use Serializer\Builder\Decoder\FileLoader\PipelineDecoderFileLoader;
use Serializer\Builder\Encoder\EncoderFactory;
use Serializer\Builder\Encoder\FileLoader\PipelineEncoderFileLoader;
use Serializer\Exception\ClassMustHaveAConstructor;
use Serializer\Exception\MissingOrInvalidProperty;
use Serializer\Exception\SerializerException;
use Serializer\Exception\UnableToLoadOrCreateCacheClass;
use Serializer\JsonSerializer;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestSerializationProvider extends ServiceProvider
{
    private const CUSTOM_ENCODERS = [
        Carbon::class => CarbonEncoder::class,
        CarbonImmutable::class => CarbonImmutableEncoder::class,
    ];

    private const CUSTOM_DECODERS = [
        Carbon::class => CarbonDecoder::class,
        CarbonImmutable::class => CarbonImmutableDecoder::class,
    ];

    public function boot(): void
    {
        $config = __DIR__ . '/../../config/serializer.php';
        $this->publishes([$config => config_path('serializer.php')]);
    }

    public function register(): void
    {
        $cache = sprintf("%s/serializer", config('cache.stores.file.path'));
        $encoder = new EncoderFactory(PipelineEncoderFileLoader::full($cache, self::CUSTOM_ENCODERS));
        $decoder = new DecoderFactory(PipelineDecoderFileLoader::full($cache, self::CUSTOM_DECODERS));

        $arraySerializer = new ArraySerializer($encoder, $decoder);

        $this->app->bind(ArraySerializer::class, fn() => $arraySerializer);
        $this->app->bind(JsonSerializer::class, fn() => new JsonSerializer($encoder, $decoder));
        $this->app->singleton(CallableDispatcher::class, SerializerCallableDispatcher::class);
        $this->app->singleton(ControllerDispatcher::class, SerializerControllerDispatcher::class);

        $classes = config('serializer', []);

        foreach ($classes as $class => $setup) {
            $this->app->bind($class, function () use ($class, $arraySerializer) {
                try {
                    return $this->decodeRequest($class, $arraySerializer);
                } catch (MissingOrInvalidProperty $e) {
                    throw $this->createBadRequest($e->getMessage());
                } catch (SerializerException $e) {
                    /**
                     * We don't want to suppress serializer exception as bad request
                     * since it is thrown when creating parser mappers.
                     * In other words, the class was not properly mapped
                     */
                    throw $e;
                } catch (Throwable $e) {
                    throw $this->createBadRequest($e->getMessage());
                }
            });
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param ArraySerializer $arraySerializer
     * @return T
     *
     * @throws MissingOrInvalidProperty
     * @throws SerializerException
     * @throws JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws ClassMustHaveAConstructor
     * @throws UnableToLoadOrCreateCacheClass
     */
    private function decodeRequest(string $class, ArraySerializer $arraySerializer)
    {
        /** @var Request $request */
        $request = $this->app->get(Request::class);

        $data = array_merge(
            $request->query->all(),
            $request->request->all(),
            $request->route()->parameters,
            $request->getContentTypeFormat() === 'json' ? json_decode((string) $request->getContent(), true) : [],
        );

        return $arraySerializer->deserialize($data, $class);
    }

    private function createBadRequest(string $message): HttpResponseException
    {
        return new HttpResponseException(
            response: new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST),
        );
    }
}
