<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use PHPUnit\Framework\TestCase;

/**
 * Class SessionTest.
 */
class SessionTest extends TestCase
{
    public function testItSetsAndGetsData(): void
    {
        $session = Session::generate();
        $session->set('legatus.identity', '1234');

        self::assertSame([
            'legatus' => [
                'identity' => '1234',
            ],
        ], $session->all());
        self::assertSame('1234', $session->get('legatus.identity'));
    }

    public function testItUnsetsData(): void
    {
        $session = Session::generate();
        $session->set('legatus.identity', '1234');
        $session->unset('legatus.identity');
        self::assertNull($session->get('legatus.identity'));
    }
}
