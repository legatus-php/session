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

use Dflydev\FigCookies\SetCookie;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class FilesystemSessionStore.
 */
final class FilesystemSessionStore extends CookieSessionStore
{
    private string $path;
    /**
     * @var Clock
     */
    private Clock $clock;

    /**
     * FilesystemSessionStore constructor.
     *
     * @param string         $path
     * @param SetCookie|null $cookie
     * @param Clock|null     $clock
     */
    public function __construct(string $path, SetCookie $cookie = null, Clock $clock = null)
    {
        parent::__construct($cookie);
        $this->path = $path;
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * @param Request $request
     * @param string  $cookieValue
     *
     * @return Session
     *
     * @throws SessionStoreError
     */
    protected function doRetrieve(Request $request, string $cookieValue): Session
    {
        $filename = $this->filename($cookieValue);
        if (!is_file($filename)) {
            throw new SessionStoreError('Could not retrieve session: session file is missing');
        }
        if ($this->cookie->getMaxAge() > 0) {
            $this->validateId($cookieValue, $this->cookie->getMaxAge());
        }
        $data = unserialize(file_get_contents($filename), [false]);

        return Session::fromArray($data);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     *
     * @return string
     *
     * @throws SessionStoreError
     */
    protected function doStore(Request $request, Response $response, Session $session): string
    {
        $array = $session->toArray();
        if ($session->isNew()) {
            $array['id'] = $this->createId();
        }
        $filename = $this->filename($array['id']);
        $data = serialize($array);
        file_put_contents($filename, $data);

        return $array['id'];
    }

    /**
     * @param string $id
     *
     * @return string
     *
     * @throws SessionStoreError
     */
    private function filename(string $id): string
    {
        if (!is_dir($this->path) && !mkdir($this->path, 0777, true) && !is_dir($this->path)) {
            throw new SessionStoreError(sprintf('Could not retrieve session: impossible to create path "%s"', $this->path));
        }

        return $this->path.DIRECTORY_SEPARATOR.$id;
    }

    /**
     * @return string
     *
     * @throws SessionStoreError
     */
    private function createId(): string
    {
        try {
            return bin2hex($this->getUInt64Time().random_bytes(8));
        } catch (\Exception $e) {
            throw new SessionStoreError('Not enough entropy');
        }
    }

    /**
     * @param string $id
     * @param int    $ttl
     *
     * @throws SessionStoreError
     */
    private function validateId(string $id, int $ttl): void
    {
        $expires = $this->getTimestamp(substr(hex2bin($id), 0, 8)) + $ttl;
        $now = $this->clock->now()->getTimestamp();
        if ($expires < $now) {
            throw new SessionStoreError('Could not retrieve session: session has expired');
        }
    }

    /**
     * @return string
     */
    private function getUInt64Time(): string
    {
        return pack('J', $this->clock->now()->getTimestamp());
    }

    /**
     * @param string $uInt64
     *
     * @return int
     */
    private function getTimestamp(string $uInt64): int
    {
        return unpack('J', $uInt64)[1];
    }
}
