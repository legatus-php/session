<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Next;

/**
 * Class FlashMiddleware.
 */
class FlashMiddleware implements MiddlewareInterface
{
    private const FLASH_ATTR = 'flash';

    /**
     * @param Request $request
     *
     * @return Flash
     */
    public static function flash(Request $request): Flash
    {
        $flash = $request->getAttribute(self::FLASH_ATTR);
        if ($flash instanceof Flash) {
            return $flash;
        }
        throw new \RuntimeException(sprintf('The requested attribute (%s) is not present in the request.
            Maybe you forgot to include the %s class in your middleware chain', self::FLASH_ATTR, __CLASS__));
    }

    /**
     * @param Request $request
     * @param Next    $next
     *
     * @return Response
     */
    public function process(Request $request, Next $next): Response
    {
        $session = SessionMiddleware::session($request);
        $flash = new Flash($session);

        return $next->handle($request->withAttribute(self::FLASH_ATTR, $flash));
    }
}
