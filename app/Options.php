<?php

namespace App;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use JsonSerializable;

class Options implements ArrayAccess, Arrayable, JsonSerializable
{
    protected $value;

    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    public function offsetExists($offset): bool
    {
        return Arr::has($this->value, $offset);
    }

    public function offsetGet($offset)
    {
        return Arr::get($this->value, $offset);
    }

    public function offsetSet($offset, $value): void
    {
        Arr::set($this->value, $offset, $value);
    }

    public function offsetUnset($offset): void
    {
        Arr::pull($this->value, $offset);
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
