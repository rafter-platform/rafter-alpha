<?php

namespace App;

use Illuminate\Support\Arr;

trait HasOptions
{
    public function getOption(string $key)
    {
        $default = Arr::get($this->defaultOptions, $key);

        $option = $this->options[$key] ?? $default;

        if (Arr::accessible($option)) {
            return array_merge($default ?? [], $option);
        }

        return $option;
    }

    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;

        $this->save();
    }
}
