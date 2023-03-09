<?php declare(strict_types=1);

namespace LaravelSerializer\Framework\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use LaravelSerializer\Framework\SerializerConfigLoader;
use Serializer\ArraySerializer;
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
        $config = SerializerConfigLoader::create();
        $arraySerializer = new ArraySerializer($config->encoder(), $config->decoder());
        $jsonSerializer = new JsonSerializer($config->encoder(), $config->decoder());

        foreach ($config->classes() as $class => $setup) {
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
