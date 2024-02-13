<?php

namespace LaravelSerializer\Framework\Commands;

use Illuminate\Console\Command;
use Serializer\ArraySerializer;
use Serializer\Builder\Decoder\DecoderFactory;
use Serializer\Builder\Decoder\FileLoader\CreateDecoderFileLoader;
use Serializer\Builder\Decoder\FileLoader\PipelineDecoderFileLoader;
use Serializer\Builder\Encoder\EncoderFactory;
use Serializer\Builder\Encoder\FileLoader\CreateEncoderFileLoader;
use Serializer\Builder\Encoder\FileLoader\PipelineEncoderFileLoader;
use Throwable;

class SerializerClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'serializer:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush the serializer cache';

    public function handle(): void
    {
        $cache = sprintf("%s/serializer", config('serializer.cache'));

        $this->deleteDirectory($cache);
        $this->components->info('Serializer cache cleared successfully.');

        $regenerate = config('serializer.regenerate', false);

        if ($regenerate) {
            $this->regenerate();
        }
    }

    private function deleteDirectory($directory): bool
    {
        if (!file_exists($directory)) {
            return true;
        }

        if (!is_dir($directory)) {
            return unlink($directory);
        }

        foreach (scandir($directory) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($directory . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($directory);
    }

    private function regenerate(): void
    {
        $classes = config('serializer.classes', []);
        $cache = sprintf("%s/serializer", config('serializer.cache'));

        $encoder = new EncoderFactory(new PipelineEncoderFileLoader(new CreateEncoderFileLoader($cache)));
        $decoder = new DecoderFactory(new PipelineDecoderFileLoader(new CreateDecoderFileLoader($cache)));

        $serializer = new ArraySerializer($encoder, $decoder);

        foreach ($classes as $class => $setup) {
            $hasEncoder = $setup['encoder'] ?? false;
            $hasDecoder = $setup['decoder'] ?? false;

            if (!$hasEncoder) {
                $this->silentTry(function () use ($encoder, $serializer, $class) {
                    $encoder->createEncoder($serializer, $class);
                }, "Encoder for class `$class`");
            }

            if (!$hasDecoder) {
                $this->silentTry(function () use ($decoder, $serializer, $class) {
                    $decoder->createDecoder($serializer, $class);
                }, "Decoder for class `$class`");
            }
        }
    }

    private function silentTry(callable $action, string $description): void
    {
        try {
            $action();
        } catch (Throwable) {
            $this->components->info("$description was not created");
        }
    }
}
