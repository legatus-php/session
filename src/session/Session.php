<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Exception;
use RuntimeException;

/**
 * Class Session.
 *
 * Models an HTTP session.
 *
 * Also contains common attributes names that are injected in the user session.
 */
class Session
{
    private string $id;
    private array $data;
    private Chronos $lastModified;
    private Chronos $startedAt;
    private bool $destroyed;

    /**
     * @return string
     */
    protected static function generateId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Exception $e) {
            throw new RuntimeException('Not enough entropy');
        }
    }

    /**
     * @return Session
     */
    public static function generate(): Session
    {
        $id = self::generateId();

        return new self($id, [], Chronos::now(), Chronos::now());
    }

    /**
     * InMemorySession constructor.
     *
     * @param string  $id
     * @param array   $data
     * @param Chronos $startedAt
     * @param Chronos $lastModified
     */
    public function __construct(string $id, array $data, Chronos $startedAt, Chronos $lastModified)
    {
        $this->id = $id;
        $this->data = $data;
        $this->lastModified = $lastModified;
        $this->startedAt = $startedAt;
        $this->destroyed = false;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $path
     * @param $value
     */
    public function set(string $path, $value): void
    {
        $this->pathAssign($this->data, $path, $value);
        $this->update();
    }

    /**
     * @param string $path
     * @param null   $default
     *
     * @return array|mixed|null
     */
    public function get(string $path, $default = null)
    {
        return $this->pathRead($this->data, $path) ?? $default;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool
    {
        return $this->pathRead($path) !== null;
    }

    /**
     * @param string $attr
     *
     * @return bool
     */
    public function has(string $attr): bool
    {
        return array_key_exists($attr, $this->data);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    public function remove(string $attr): void
    {
        unset($this->data[$attr]);
        $this->update();
    }

    public function regenerate(): void
    {
        $this->id = self::generateId();
        $this->update();
    }

    public function destroy(): void
    {
        $this->destroyed = true;
        $this->update();
    }

    /**
     * @return bool
     */
    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    /**
     * @return ChronosInterface
     */
    public function lastModified(): ChronosInterface
    {
        return $this->lastModified;
    }

    /**
     * @param int $ttl
     *
     * @return bool
     */
    public function isExpired(int $ttl): bool
    {
        return $this->lastModified->addSeconds($ttl)->isPast();
    }

    public function startedAt(): ChronosInterface
    {
        return $this->startedAt;
    }

    private function update(): void
    {
        $this->lastModified = Chronos::now();
    }

    /**
     * @param array  $arr
     * @param string $path
     * @param $value
     */
    protected function pathAssign(array &$arr, string $path, $value): void
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            if (is_numeric($key)) {
                $key = (int) $key;
            }
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    /**
     * @param array  $data
     * @param string $path
     *
     * @return array|mixed|null
     */
    protected function pathRead(?array $data, string $path)
    {
        $segments = explode('.', $path);
        while (count($segments) > 0) {
            if ($data === null) {
                return null;
            }
            $segment = array_shift($segments);

            if (is_numeric($segment)) {
                $segment = (int) $segment;
            }
            $data = $data[$segment] ?? null;
        }

        return $data;
    }
}
