<?php

declare(strict_types=1);

namespace LaravelSerializer\Encoder;

use Carbon\Carbon;
use DateTimeInterface;
use Serializer\Encoder;

class CarbonEncoder extends Encoder
{
    /**
     * @inheritdoc
     * @param Carbon $object
     */
    public function encode(object $object): array|string|int|float|bool|null
    {
        return $object->format(DateTimeInterface::ISO8601);
    }
}
