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

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Class SessionMiddleware.
 */
final class SessionMiddleware implements MiddlewareInterface
{
    private SessionStore $store;

    /**
     * SessionMiddleware constructor.
     *
     * @param SessionStore $store
     */
    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    /**
     * @param Request $request
     * @param Handler $handler
     *
     * @return Response
     *
     * @throws SessionStoreError
     */
    public function process(Request $request, Handler $handler): Response
    {
        try {
            $session = $this->store->retrieve($request);
        } catch (SessionStoreError $e) {
            $session = $this->store->create($request);
        }

        $response = $handler->handle(SessionContext::initialize($request, $session));

        return $this->store->store($request, $response, $session);
    }
}
