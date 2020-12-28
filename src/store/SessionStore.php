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

/**
 * The SessionStore provides a contract for implementing different session
 * storage mechanisms.
 */
interface SessionStore
{
    /**
     * Retrieves a session from the store.
     *
     * @param Request $request
     *
     * @return Session
     *
     * @throws SessionStoreError when a session cannot be retrieved
     */
    public function retrieve(Request $request): Session;

    /**
     * Creates a new session in the store.
     *
     * @param Request $request
     *
     * @return Session
     *
     * @throws SessionStoreError when a new session cannot be created
     */
    public function create(Request $request): Session;

    /**
     * Stores a session.
     *
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     *
     * @return Response
     *
     * @throws SessionStoreError when the session cannot be saved
     */
    public function store(Request $request, Response $response, Session $session): Response;
}
