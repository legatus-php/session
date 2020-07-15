<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Legatus\Http\Session\Session;
use Legatus\Http\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

/**
 * @param Request $request
 *
 * @return Session
 */
function session(Request $request): Session
{
    $session = $request->getAttribute(SessionMiddleware::SESSION_ATTR);
    if ($session instanceof Session) {
        return $session;
    }
    throw new RuntimeException(sprintf('The requested attribute (%s) is not present in the request.
            Maybe you forgot to include the %s class in your middleware chain', SessionMiddleware::SESSION_ATTR, SessionMiddleware::class));
}
