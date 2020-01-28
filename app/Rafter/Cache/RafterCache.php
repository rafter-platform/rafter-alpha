<?php

namespace App\Rafter\Cache;

use Carbon\Carbon;
use DateTime;
use Exception;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\InteractsWithTime;

class RafterCache implements Store
{
    use InteractsWithTime, RetrievesMultipleKeys;

    /**
     * Firestore client.
     *
     * @var Google\Cloud\Firestore\FirestoreClient
     */
    protected $client;

    /**
     * Collection name.
     *
     * @var string
     */
    protected $collection;

    /**
     * Prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new firestore cache store instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $collection
     * @param  string  $prefix
     * @return void
     */
    public function __construct(FirestoreClient $client, $collection = 'cache', $prefix = '')
    {
        $this->client = $client;
        $this->collection = $collection;
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {
        $item = $this->find($key);

        if (empty($item)) {
            return null;
        }

        if ($this->expired($item)) {
            $this->forget($key);

            return null;
        }

        return $item->value;
    }

    /**
     * Check if item expired.
     *
     * @param object $item
     * @return bool
     */
    public function expired($item)
    {
        return $item->expiration->get()->getTimestamp() < time();
    }

    /**
     * Find item by key.
     *
     * @param string $key
     * @return object
     */
    public function find($key)
    {
        $data = $this
            ->getCollection()
            ->document($this->getPrefix().$key)
            ->snapshot()
            ->data();

        if (empty($data)) {
            return null;
        }

        $data['value'] = unserialize($data['value']);

        return (object) $data;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int|Timestamp  $seconds
     * @return bool
     */
    public function put($key, $value, $seconds)
    {
        $payload = [
            'value' => serialize($value),
            'expiration' => $this->expiration($seconds),
            'key' => $key,
            'prefix' => $this->getPrefix(),
        ];

        $this
            ->getCollection()
            ->document($this->getPrefix().$key)
            ->set($payload);

        return true;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $item = $this->find($key);

        if (empty($item)) {
            $this->put($key, $value, 0);

            return $value;
        }

        $value = intval($item->value) + $value;

        $this->put($key, $value, $item->expiration);

        return $value;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        $this->getCollection()->document($this->getPrefix().$key)->delete();
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $items = $this
            ->getCollection()
            ->where('prefix', '=', $this->getPrefix())
            ->documents();

        foreach ($items as $item) {
            $item->reference()->delete();
        }

        return true;
    }
    /**
     * Get the expiration time based on the given seconds.
     *
     * @param  int|Timestamp  $seconds
     * @return Timestamp
     */
    protected function expiration($seconds)
    {
        if ($seconds instanceof Timestamp) {
            return $seconds;
        }
        $timestamp = $this->availableAt($seconds);
        $timestamp = $seconds === 0 || $timestamp > 9999999999 ? 9999999999 : $timestamp;

        return new Timestamp(new DateTime("@$timestamp"));
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get a collection reference.
     *
     * @return CollectionReference
     */
    protected function getCollection()
    {
        return $this->client->collection($this->collection);
    }
}
