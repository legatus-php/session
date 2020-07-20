<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use JsonException;
use Legatus\Support\Cipher;
use Redis;

/**
 * Class RedisSessionStorage.
 */
final class RedisSessionStorage extends BaseSessionStorage
{
    private Redis $redis;
    private string $namespace;

    /**
     * RedisSessionStorage constructor.
     *
     * @param Cipher $cipher
     * @param Redis  $redis
     * @param string $namespace
     */
    public function __construct(Cipher $cipher, Redis $redis, string $namespace = 'legatus:sessions')
    {
        parent::__construct($cipher);
        $this->redis = $redis;
        $this->namespace = $namespace;
    }

    /**
     * @param string $id
     *
     * @return array|null
     *
     * @throws JsonException
     * @throws \Legatus\Support\ExpiredCipher
     * @throws \Legatus\Support\InvalidCipher
     */
    public function retrieve(string $id): ?array
    {
        $key = $this->key($id);
        if (!$this->redis->exists($key)) {
            return null;
        }

        return $this->decrypt($this->redis->get($id));
    }

    /**
     * @param string $id
     * @param array  $data
     *
     * @throws JsonException
     */
    public function store(string $id, array $data): void
    {
        $key = $this->key($id);
        $this->redis->set($key, $this->encrypt($data));
    }

    /**
     * @param string $id
     */
    public function delete(string $id): void
    {
        $key = $this->key($id);
        $this->redis->del([$key]);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function key(string $id): string
    {
        return sprintf('%s:%s', $this->namespace, $id);
    }
}
