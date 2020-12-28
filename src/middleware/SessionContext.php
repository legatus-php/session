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

use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

/**
 * Class SessionContext helps store the session in a request.
 */
class SessionContext
{
    protected const SESSION_ATTR = 'session';

    /**
     * @param Request $request
     * @param Session $session
     *
     * @return Request
     */
    public static function initialize(Request $request, Session $session): Request
    {
        return $request->withAttribute(self::SESSION_ATTR, $session);
    }

    /**
     * @param Request $request
     *
     * @return Session
     */
    public static function from(Request $request): Session
    {
        $session = $request->getAttribute(self::SESSION_ATTR);
        if (!$session instanceof Session) {
            throw new RuntimeException('No session has been found in the request');
        }

        return $session;
    }
}
