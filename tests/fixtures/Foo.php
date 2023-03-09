<?php

namespace App\Models;

class Foo
{
    private string $a;
    private string $b;
    private Type $type;

    public function __construct(string $a, string $b, Type $type)
    {
        $this->a = $a;
        $this->b = $b;
        $this->type = $type;
    }

    public function getA(): string
    {
        return $this->a;
    }

    public function getB(): string
    {
        return $this->b;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
