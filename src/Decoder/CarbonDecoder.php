<?php

declare(strict_types=1);

namespace LaravelSerializer\Decoder;

use Carbon\Carbon;
use Exception;
use Serializer\Decoder;
use Serializer\Exception\InvalidDateTimeProperty;
use Throwable;

class CarbonDecoder extends Decoder
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function decode(mixed $data, ?string $propertyName = null): object
    {
        try {
            return new Carbon((string) $data);
        } catch (Throwable $e) {
            throw new InvalidDateTimeProperty($e, (string) $propertyName);
        }
    }
}
