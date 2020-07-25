<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Brick\DateTime\Instant;
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
    private Instant $lastModified;
    private Instant $startedAt;
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

        return new self($id, [], Instant::now(), Instant::now());
    }

    /**
     * InMemorySession constructor.
     *
     * @param string  $id
     * @param array   $data
     * @param Instant $startedAt
     * @param Instant $lastModified
     */
    public function __construct(string $id, array $data, Instant $startedAt, Instant $lastModified)
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
     * @param string     $path
     * @param mixed|null $default
     *
     * @return array|mixed|null
     */
    public function get(string $path, $default = null)
    {
        return $this->pathRead($this->data, $path) ?? $default;
    }

    /**
     * @param string $path
     */
    public function unset(string $path): void
    {
        $this->pathAssign($this->data, $path, null);
        $this->update();
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool
    {
        return $this->pathRead($this->data, $path) !== null;
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
     * @return Instant
     */
    public function lastModified(): Instant
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
        return $this->lastModified->plusSeconds($ttl)->isPast();
    }

    public function startedAt(): Instant
    {
        return $this->startedAt;
    }

    private function update(): void
    {
        $this->lastModified = Instant::now();
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
