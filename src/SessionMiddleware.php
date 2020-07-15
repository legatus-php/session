<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\Modifier\SameSite;
use Dflydev\FigCookies\SetCookie;
use Legatus\Http\Session\Store\SessionStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class SessionMiddleware.
 */
class SessionMiddleware implements MiddlewareInterface
{
    public const SESSION_ATTR = 'session';

    private SessionStore $store;
    private SetCookie $cookie;
    private int $sessionTtl;

    /**
     * SessionMiddleware constructor.
     *
     * @param SessionStore $store
     * @param SetCookie    $cookie
     * @param int          $sessionTtl
     */
    public function __construct(SessionStore $store, SetCookie $cookie = null, int $sessionTtl = 3600)
    {
        $this->store = $store;
        $this->sessionTtl = $sessionTtl;
        $this->cookie = $cookie ?? $this->defaultCookie();
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     */
    public function process(Request $request, Next $next): Response
    {
        $cookie = Cookies::fromRequest($request)->get($this->cookie->getName());

        $session = $this->getOrCreateSession($cookie);

        $id = $session->getId();
        $lastModified = $session->lastModified();

        // If session is expired, regenerate it.
        if ($session->isExpired($this->sessionTtl)) {
            $session->regenerate();
        }

        $response = $next->handle(
            $request->withAttribute(self::SESSION_ATTR, $session)
        );

        // If the session id changed it means has been regenerated.
        // So we remove the old one.
        if ($id !== $session->getId()) {
            $this->store->remove($id);
        }

        // If session is destroyed, we return early deleting cookie.
        if ($session->isDestroyed()) {
            $this->store->destroy($session);

            return $this->removeSessionFrom($response);
        }

        // If the session has changed, we save it and modify response.
        if ($lastModified !== $session->lastModified()) {
            $this->store->save($session);
            $response = $this->updateSessionId($response, $id);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @param string   $id
     *
     * @return Response
     */
    private function updateSessionId(Response $response, string $id): Response
    {
        return FigResponseCookies::set($response, $this->cookie->withValue($id));
    }

    /**
     * @param Cookie|null $cookie
     *
     * @return Session
     */
    private function getOrCreateSession(?Cookie $cookie): Session
    {
        if (!$cookie instanceof Cookie) {
            return $this->store->new();
        }
        $session = $this->store->fetch($cookie->getValue() ?? '');
        if ($session instanceof Session) {
            return $session;
        }

        return $this->store->new();
    }

    /**
     * Returns a very secure default cookie.
     *
     * @return SetCookie
     */
    private function defaultCookie(): SetCookie
    {
        return SetCookie::create('lgsid')
            ->withPath('/')
            ->withSecure(true)
            ->withSameSite(SameSite::strict())
            ->withHttpOnly(true)
            ->withMaxAge($this->sessionTtl);
    }

    /**
     * @param Response $response
     *
     * @return Response
     */
    private function removeSessionFrom(Response $response): Response
    {
        return FigResponseCookies::remove($response, $this->cookie->getName());
    }
}
