<?php

namespace LaravelSerializer\Framework\Dispatcher;

use Illuminate\Http\JsonResponse;
use Serializer\ArraySerializer;

class SerializeResponseWrapper
{
    public function __construct(private ArraySerializer $serializer)
    {
        // promoted
    }

    public function respond($response)
    {
        return $this->isSerializable($response)
            ? new JsonResponse($this->serializer->serialize($response))
            : $response;
    }

    private function isSerializable($response): bool
    {
        $classes = config('serializer', []);

        return is_object($response) && array_key_exists(get_class($response), $classes);
    }
}
