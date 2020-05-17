<?php

namespace App;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Options implements ArrayAccess, Arrayable, JsonSerializable
{
    protected $value;

    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->value[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->value[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->value[$offset]);
    }

    public function toArray(): array
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
