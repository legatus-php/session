<?php

declare(strict_types=1);

/*
 * @project Legatus Session
 * @link https://github.com/legatus-php/session
 * @package legatus/session
 * @author Matias Navarro-Carter mnavarrocarter@gmail.com
 * @license MIT
 * @copyright 2021 Matias Navarro-Carter
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use InvalidArgumentException;

/**
 * Class Session represents an HTTP Session.
 */
class Session
{
    private string $id;
    private array $data;

    private const FLASH_KEY = '_flashes';

    /**
     * @return Session
     */
    public static function create(): Session
    {
        return new self('', []);
    }

    /**
     * @param array $array
     *
     * @return Session
     */
    public static function fromArray(array $array): Session
    {
        $id = $array['id'] ?? null;
        $data = $array['data'] ?? null;
        if ($id === null) {
            throw new InvalidArgumentException('Session id is missing from array');
        }
        if ($data === null) {
            throw new InvalidArgumentException('Session data is missing from array');
        }

        return new self($id, $data);
    }

    /**
     * Session constructor.
     *
     * @param string $id
     * @param array  $data
     */
    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function put(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function flash(string $key, $value): void
    {
        if (!isset($this->data[self::FLASH_KEY])) {
            $this->data[self::FLASH_KEY] = [];
        }
        $this->data[self::FLASH_KEY][$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        if (array_key_exists($key, $this->data[self::FLASH_KEY] ?? [])) {
            $value = $this->data[self::FLASH_KEY][$key];
            unset($this->data[self::FLASH_KEY][$key]);

            return $value;
        }

        return null;
    }

    /**
     * @param string $key
     */
    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data)
            || array_key_exists($key, $this->data[self::FLASH_KEY] ?? []);
    }

    /**
     * @return $this
     */
    public function regenerate(): Session
    {
        $this->id = '';

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->id === '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
        ];
    }
}
