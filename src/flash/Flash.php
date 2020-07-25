<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

/**
 * Interface Flash.
 */
class Flash
{
    private const KEY = 'flashes';

    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var array
     */
    private array $current;

    /**
     * Flash constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        // We take the flashes of the previous request and empty the current
        $this->current = (array) $session->get(self::KEY, []);
        $this->session->set(self::KEY, []);
    }

    /**
     * @param string $key
     * @param string $message
     */
    public function add(string $key, string $message): void
    {
        $flashes = $this->session->get(self::KEY, []);
        $flashes[$key] = $message;
        $this->session->set(self::KEY, $flashes);
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function read(string $key): ?string
    {
        return $this->current[$key] ?? null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->current);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->current;
    }

    /**
     * Clears the flashes.
     */
    public function clear(): void
    {
        $this->current = [];
    }
}
