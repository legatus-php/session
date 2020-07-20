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
$cipher = new Legatus\Support\DefuseCipher($key);
$adapter = new Legatus\Http\FilesystemSessionStorage($cipher);
$store = new Legatus\Http\StorageSessionManager($adapter);
$middleware = new Legatus\Http\SessionMiddleware($store);

$middleware->process($request, $handler);

// Then, in subsequent middleware you can:
Legatus\Http\SessionMiddleware::session($request)->mutate(fn ($data) => $data['count'] ? $data['count']++ : 1);
