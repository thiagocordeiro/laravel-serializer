<?php

namespace LaravelSerializer\Framework\Dispatcher;

use Illuminate\Http\JsonResponse;
use LaravelSerializer\Framework\SerializerResponse;
use Serializer\ArraySerializer;

readonly class SerializeResponseWrapper
{
    public function __construct(private ArraySerializer $serializer)
    {
    }

    public function respond($response)
    {
        if ($response instanceof SerializerResponse) {
            return new JsonResponse(
                data: $this->serializer->serialize($response->data),
                status: $response->status,
                headers: $response->headers,
            );
        }

        return $this->isSerializable($response)
            ? new JsonResponse($this->serializer->serialize($response))
            : $response;
    }

    private function isSerializable($response): bool
    {
        $classes = config('serializer.classes', []);

        /**
         * Laravel does not allow traversable responses, so we can assume it should be serialized
         */
        if (is_iterable($response)) {
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
