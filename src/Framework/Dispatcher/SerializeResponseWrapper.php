<?php

namespace LaravelSerializer\Framework\Dispatcher;

use Illuminate\Http\JsonResponse;
use Serializer\ArraySerializer;
use Traversable;

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

        /**
         * Laravel does not allow traversable responses, so we can assume it should be serialized
         */
        if ($response instanceof Traversable) {
            return true;
        }

        /**
         * if it is not an object then it is not something we can serialize
         */
        if (false === is_object($response)) {
            return false;
        }

        return array_key_exists(get_class($response), $classes);
    }
}
