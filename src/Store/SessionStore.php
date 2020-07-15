<?php
declare(strict_types=1);

namespace Legatus\Http\Session\Store;

use Legatus\Http\Session\Session;

/**
 * Interface SessionStore
 * @package Legatus\Http\Session\Manager
 */
interface SessionStore
{
    /**
     * @param string $id
     * @return Session|null
     */
    public function fetch(string $id): ?Session;

    /**
     * @return Session
     */
    public function new(): Session;

    /**
     * @param Session $session
     */
    public function save(Session $session): void;

    /**
     * @param Session $session
     */
    public function destroy(Session $session): void;

    /**
     * @param string $id
     */
    public function remove(string $id): void;
}