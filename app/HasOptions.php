<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasOptions
{
    /**
     * Get an option from the `options` column, falling back to any value provided in a
     * $defaultOptions property on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getOption(string $key)
    {
        $default = Arr::get($this->defaultOptions ?? [], $key);

        $option = $this->options[$key] ?? $default;

        if (Arr::accessible($option)) {
            return array_merge($default ?? [], $option);
        }

        return $option;
    }

    /**
     * Set an option in the `options` column and save the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;

        $this->save();
    }

    /**
     * @inheritDoc
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute') || null !== $this->getOption($key);
    }

    /**
     * @inheritDoc
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->getOption($key) ?: $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }
}
