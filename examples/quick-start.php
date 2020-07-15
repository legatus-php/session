<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../vendor/autoload.php';

$key = Defuse\Crypto\Key::createNewRandomKey();
$adapter = new Legatus\Http\Session\Store\Adapter\FilesystemAdapter($key);
$store = new Legatus\Http\Session\Store\AdaptableSessionStore($adapter);
$middleware = new Legatus\Http\Session\SessionMiddleware($store);

$middleware->process($request, $handler);

// Then, in next middleware you can:

Legatus\Http\session($request)->mutate(fn ($data) => $data['count'] ? $data['count']++ : 1);
