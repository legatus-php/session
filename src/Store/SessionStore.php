<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session\Store;

use Legatus\Http\Session\Session;

/**
 * Interface SessionStore.
 */
interface SessionStore
{
    /**
     * @param string $id
     *
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
