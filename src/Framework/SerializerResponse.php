<?php

namespace LaravelSerializer\Framework;

use Illuminate\Http\JsonResponse;

class SerializerResponse extends JsonResponse
{
    public function __construct(
        public readonly mixed $serializable,
        $status = 200,
        $headers = [],
    ) {
        parent::__construct($serializable, $status, $headers);
    }
}
