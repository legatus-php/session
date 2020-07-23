<?php

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface Redis
{
    /**
     * @param string|string[] $key
     *
     * @return bool|int
     */
    public function exists($key);

    /**
     * @param string       $key
     * @param string|mixed $value   string if not used serializer
     * @param int|array    $timeout [optional] Calling setex() is preferred if you want a timeout.<br>
     *
     * @return bool
     */
    public function set($key, $value, $timeout): bool;

    /**
     * @param string $key
     *
     * @return string|mixed|bool if key didn't exist, FALSE is returned
     */
    public function get($key);

    /**
     * @param int|string|array $key1         An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @param int|string       ...$otherKeys
     *
     * @return int Number of keys deleted
     */
    public function del($key1, ...$otherKeys): int;
}
