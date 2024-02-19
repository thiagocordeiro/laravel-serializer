<?php

namespace LaravelSerializer\Framework;

readonly class SerializerResponse
{
    public function __construct(
        public object $data,
        public int $status = 200,
        public array $headers = [],
    ) {
    }
}
