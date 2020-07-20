<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

/**
 * Class SessionStorage.
 */
interface SessionStorage
{
    /**
     * @param string $id
     *
     * @return array|null
     */
    public function retrieve(string $id): ?array;

    /**
     * @param string $id
     * @param array  $data
     */
    public function store(string $id, array $data): void;

    /**
     * @param string $id
     */
    public function delete(string $id): void;
}
