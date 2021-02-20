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
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;

/**
 * Class Session represents an HTTP Session.
 */
class Session
{
    private string $id;
    private array $data;
    private int $created;

    private const FLASH_KEY = '_flashes';

    /**
     * @return Session
     */
    public static function create(): Session
    {
        return new self('', time(), []);
    }

    /**
     * @param array $array
     *
     * @return Session
     */
    public static function fromArray(array $array): Session
    {
        $id = $array['id'] ?? null;
        $created = $array['created'] ?? null;
        $data = $array['data'] ?? null;
        if ($id === null) {
            throw new InvalidArgumentException('Session id is missing from array');
        }
        if ($created === null) {
            throw new InvalidArgumentException('Session creation time is missing from array');
        }
        if ($data === null) {
            throw new InvalidArgumentException('Session data is missing from array');
        }

        return new self($id, (int) $created, $data);
    }

    /**
     * Session constructor.
     *
     * @param string $id
     * @param int    $created
     * @param array  $data
     */
    public function __construct(string $id, int $created, array $data)
    {
        $this->id = $id;
        $this->created = $created;
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param Clock|null $clock
     *
     * @return $this
     */
    public function regenerate(Clock $clock = null): Session
    {
        $clock = $clock ?? SystemClock::fromUTC();
        $this->id = '';
        $this->created = $clock->now()->getTimestamp();

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
            'created' => $this->created,
            'data' => $this->data,
        ];
    }
}
