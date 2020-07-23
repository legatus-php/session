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

/**
 * Class AdaptableSessionStore.
 */
final class StorageSessionManager implements SessionManager
{
    private SessionStorage $storage;

    /**
     * AdaptableSessionStore constructor.
     *
     * @param SessionStorage $storage
     */
    public function __construct(SessionStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $id
     *
     * @return Session|null
     */
    public function fetch(string $id): ?Session
    {
        $data = $this->storage->retrieve($id);
        if ($data === null) {
            return null;
        }

        return new Session(
            $id,
            $data['data'],
            Instant::of($data['startedAt']),
            Instant::of($data['lastModified'])
        );
    }

    /**
     * @return Session
     */
    public function new(): Session
    {
        return Session::generate();
    }

    /**
     * @param Session $session
     */
    public function save(Session $session): void
    {
        $data = [];
        $data['data'] = $session->all();
        $data['startedAt'] = $session->startedAt()->getEpochSecond();
        $data['lastModified'] = $session->lastModified()->getEpochSecond();
        $this->storage->store($session->getId(), $data);
    }

    /**
     * @param Session $session
     */
    public function destroy(Session $session): void
    {
        $this->storage->delete($session->getId());
    }

    /**
     * @param string $id
     */
    public function remove(string $id): void
    {
        $this->storage->delete($id);
    }
}
