<?php

declare(strict_types=1);

namespace LaravelSerializer\Encoder;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Serializer\Encoder;

class CarbonImmutableEncoder extends Encoder
{
    /**
     * @inheritdoc
     * @param CarbonImmutable $object
     */
    public function encode(object $object): array|string|int|float|bool|null
    {
        return $object->format(DateTimeInterface::ATOM);
    }
}
