<?php

namespace App;

use Dotenv\Loader\Lines;
use Dotenv\Loader\Parser;

class EnvVars
{
    /**
     * Store of the key/value pairs.
     *
     * @var array
     */
    protected $vars = [];

    /**
     * Create a new instance of EnvVars, with optional initial vars
     *
     * @param array $vars
     */
    public function __construct(array $vars = [])
    {
        foreach ($vars as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Parse a .env-like string into environment variables.
     *
     * Functionality borrowed from DotEnv library.
     *
     * @param string $string
     * @return self
     */
    public static function fromString($string)
    {
        $self = new static;
        $entries = Lines::process(preg_split("/(\r\n|\n|\r)/", $string));

        foreach ($entries as $entry) {
            [$key, $value] = Parser::parse($entry);

            $self->set($key, $value->getChars());
        }

        return $self;
    }

    /**
     * Set an environment variables
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->vars[$key] = (string) $value;

        return $this;
    }

    /**
     * Inject multiple variables.
     *
     * @param array $addition
     * @return self
     */
    public function inject(array $addition)
    {
        foreach ($addition as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get an env var, or all env vars if key is omitted.
     *
     * @param string $key (optional)
     * @return string|null
     */
    public function get($key = '')
    {
        if (empty($key)) {
            return $this->vars;
        }

        return $this->vars[$key] ?? null;
    }

    /**
     * Whether an env var exists.
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key)
    {
        return !empty($this->get($key));
    }

    /**
     * Return all variables in the format of:
     * array<{key: string, value: string}>
     *
     * @return array
     */
    public function all($keyName = 'name', $valueName = 'value')
    {
        $all = [];

        foreach ($this->vars as $key => $value) {
            $all[] = [
                $keyName => $key,
                $valueName => $value,
            ];
        }

        return $all;
    }

    /**
     * Get the variables listed as a string, in a .env-like manner.
     *
     * @return string
     */
    public function toString()
    {
        $string = "";

        foreach ($this->vars as $key => $value) {
            $string .= "$key='$value'\n";
        }

        return trim($string);
    }

    public function __toString()
    {
        return $this->toString();
    }
}
