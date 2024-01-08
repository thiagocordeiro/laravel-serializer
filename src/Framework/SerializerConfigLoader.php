<?php

namespace LaravelSerializer\Framework;

use LaravelSerializer\ConfigLoader;
use Serializer\Builder\Decoder\DecoderFactory;
use Serializer\Builder\Decoder\FileLoader\PipelineDecoderFileLoader;
use Serializer\Builder\Encoder\EncoderFactory;
use Serializer\Builder\Encoder\FileLoader\PipelineEncoderFileLoader;

class SerializerConfigLoader implements ConfigLoader
{
    private array $classes;
    private string $cacheFolder;
    private EncoderFactory $encoder;
    private DecoderFactory $decoder;

    private function __construct(array $classes, string $cacheFolder)
    {
        $this->classes = $classes;
        $this->cacheFolder = $cacheFolder;
        $this->encoder = new EncoderFactory(PipelineEncoderFileLoader::full($this->cacheFolder));
        $this->decoder = new DecoderFactory(PipelineDecoderFileLoader::full($this->cacheFolder));
    }

    public static function create(): SerializerConfigLoader
    {
        return new SerializerConfigLoader(
            classes: require config_path('serializer.php') ?? [],
            cacheFolder: storage_path('/app/serializer'),
        );
    }

    public function classes(): array
    {
        return $this->classes;
    }

    public function encoder(): EncoderFactory
    {
        return $this->encoder;
    }

    public function decoder(): DecoderFactory
    {
        return $this->decoder;
    }
}
