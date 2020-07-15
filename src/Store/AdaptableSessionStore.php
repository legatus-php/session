<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session\Store;

use Cake\Chronos\Chronos;
use Legatus\Http\Session\InMemorySession;
use Legatus\Http\Session\Session;
use Legatus\Http\Session\Store\Adapter\StorageAdapter;

/**
 * Class AdaptableSessionStore.
 */
final class AdaptableSessionStore implements SessionStore
{
    /**
     * @var StorageAdapter
     */
    private StorageAdapter $adapter;

    /**
     * AdaptableSessionStore constructor.
     *
     * @param StorageAdapter $adapter
     */
    public function __construct(StorageAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param string $id
     *
     * @return Session|null
     */
    public function fetch(string $id): ?Session
    {
        $data = $this->adapter->retrieve($id);
        if ($data === null) {
            return null;
        }

        return new InMemorySession(
            $id,
            $data['data'],
            Chronos::createFromTimestamp($data['startedAt']),
            Chronos::createFromTimestamp($data['lastModified'])
        );
    }

    /**
     * @return Session
     */
    public function new(): Session
    {
        return InMemorySession::generate();
    }

    /**
     * @param Session $session
     */
    public function save(Session $session): void
    {
        $data = [];
        $data['data'] = $session->all();
        $data['startedAt'] = $session->startedAt()->getTimestamp();
        $data['lastModified'] = $session->lastModified()->getTimestamp();
        $this->adapter->store($session->getId(), $data);
    }

    /**
     * @param Session $session
     */
    public function destroy(Session $session): void
    {
        $this->adapter->delete($session->getId());
    }

    /**
     * @param string $id
     */
    public function remove(string $id): void
    {
        $this->adapter->delete($id);
    }
}
