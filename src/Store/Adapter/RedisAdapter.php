<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session\Store\Adapter;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use JsonException;
use Legatus\Support\Crypto\Cipher\Cipher;
use Redis;

/**
 * Class RedisAdapter.
 */
final class RedisAdapter extends BaseAdapter
{
    private Redis $redis;
    private string $namespace;

    /**
     * RedisAdapter constructor.
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
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws JsonException
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
     * @throws EnvironmentIsBrokenException
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
