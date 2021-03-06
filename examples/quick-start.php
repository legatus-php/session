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

use Legatus\Http\FilesystemSessionStore;
use Legatus\Http\SessionContext;
use Legatus\Http\SessionMiddleware;

require_once __DIR__.'/../vendor/autoload.php';

$store = new FilesystemSessionStore('/temp/sessions');
$middleware = new SessionMiddleware($store);

$middleware->process($request, $handler);

// Then, in subsequent middleware you can:
SessionContext::from($request)->put('auth.user_id', 'some-id');
