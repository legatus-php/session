<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session;

use Cake\Chronos\Chronos;
use Cake\Chronos\ChronosInterface;
use Exception;
use RuntimeException;

/**
 * Class InMemorySession.
 */
final class InMemorySession implements Session
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
     * @param string $attr
     * @param null   $default
     *
     * @return mixed|null
     */
    public function get(string $attr, $default = null)
    {
        return $this->data[$attr] ?? $default;
    }

    /**
     * @param callable $mutator
     *
     * @return Session
     */
    public function mutate(callable $mutator): Session
    {
        $this->data = $mutator($this->data);
        $this->update();
        return $this;
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
}
