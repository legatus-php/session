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
        $this->clock = $clock ?? SystemClock::fromUTC();
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

        $data = unserialize(file_get_contents($filename), [false]);
        $session = Session::fromArray($data);

        $maxAge = $this->cookie->getMaxAge();
        $now = $this->clock->now()->getTimestamp();
        $isSessionExpired = $maxAge > 0 && ($session->getCreated() + $maxAge) < $now;
        if ($isSessionExpired) {
            throw new SessionStoreError('Could not retrieve session: session has expired');
        }

        return $session;
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
     * @param Session  $session
     * @param Response $response
     *
     * @return Response
     *
     * @throws SessionStoreError
     */
    public function destroy(Session $session, Response $response): Response
    {
        if ($session->getId() !== '') {
            $filename = $this->filename($session->getId());
            unlink($filename);
        }

        return parent::destroy($session, $response);
    }
}
