<?php

namespace LaravelSerializer;

use Serializer\Builder\Decoder\DecoderFactory;
use Serializer\Builder\Encoder\EncoderFactory;

interface ConfigLoader
{
    public function classes(): array;

    public function encoder(): EncoderFactory;

    public function decoder(): DecoderFactory;
}
