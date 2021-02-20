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

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class CookieSessionStore.
 */
abstract class CookieSessionStore implements SessionStore
{
    /**
     * @var SetCookie
     */
    protected SetCookie $cookie;

    /**
     * CookieSessionStore constructor.
     *
     * @param SetCookie|null $cookie
     */
    public function __construct(SetCookie $cookie = null)
    {
        $this->cookie = $cookie ?? SessionCookie::default();
    }

    public function retrieve(Request $request): Session
    {
        $value = FigRequestCookies::get($request, $this->cookie->getName())->getValue();
        if ($value === null) {
            throw new SessionStoreError('Could not retrieve session: no cookie value is present');
        }

        return $this->doRetrieve($request, $value);
    }

    /**
     * @param Request $request
     *
     * @return Session
     */
    public function create(Request $request): Session
    {
        return Session::create();
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     *
     * @return Response
     */
    public function store(Request $request, Response $response, Session $session): Response
    {
        $cookieValue = $this->doStore($request, $response, $session);

        return FigResponseCookies::set($response, $this->cookie->withValue($cookieValue));
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     *
     * @return string The cookie value
     */
    abstract protected function doStore(Request $request, Response $response, Session $session): string;

    /**
     * @param Request $request
     * @param string  $cookieValue
     *
     * @return Session
     *
     * @throws SessionStoreError
     */
    abstract protected function doRetrieve(Request $request, string $cookieValue): Session;

    /**
     * @return string
     *
     * @throws SessionStoreError
     */
    protected function createId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Exception $e) {
            throw new SessionStoreError('Could not store session: not enough entropy for identifier', 0, $e);
        }
    }

    /**
     * @param Session  $session
     * @param Response $response
     *
     * @return Response
     */
    public function destroy(Session $session, Response $response): Response
    {
        return FigResponseCookies::remove($response, $this->cookie->getName());
    }
}
