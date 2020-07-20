<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Defuse\Crypto\Key;
use Legatus\Support\DefuseCipher;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

/**
 * Class FilesystemAdapterTest.
 */
class FilesystemAdapterTest extends TestCase
{
    public function testItStoresAndFetchesSessionData(): void
    {
        $key = Key::createNewRandomKey();
        $cipher = new DefuseCipher($key);
        $fs = FileSystem::factory('vfs://');
        $fs->mount();
        $adapter = new FilesystemSessionStorage($cipher, 'vfs://');
        $adapter->store('id', [
            'count' => 1,
        ]);
        $data = $adapter->retrieve('id');
        self::assertSame(['count' => 1], $data);
        self::assertFileExists('vfs://a56145270ce6b3bebd1dd012b73948677dd618d496488bc608a3cb43ce3547dd');
    }
}
