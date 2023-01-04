<?php

namespace Chs\Geoname\Util;

trait SelfCachingTrait {
    protected static array $_cache = [];

    /**
     * Add the data to the cache array under the key value
     *
     * @param array $key
     * @param $data
     * @return void
     */
    protected static function setCache(array $key, $data) {
        self::$_cache[self::generateKey($key)] = $data;
    }

    /**
     * @param array $key
     * @return mixed
     * @throws \InvalidArgumentException if provided key has no cache value
     */
    protected static function getCache(array $key) {
        if (self::hasCache($key)) return self::$_cache[self::generateKey($key)];
        throw new \InvalidArgumentException('Invalid cache key');
    }

    /**
     * Return if the class cache has the passed key in it
     *
     * @param array $key
     * @return bool
     */
    protected static function hasCache(array $key) {
        return isset(self::$_cache[self::generateKey($key)]);
    }

    /**
     * Generate a key value from the array values
     *
     * @param array $key_items
     * @return string
     */
    private static function generateKey(array $key_items) {
        return implode(':|:', $key_items);
    }

    /**
     * Clear the class cache
     *
     * @return void
     */
    protected static function clearCache() {
        self::$_cache = [];
    }
}