<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session\Store\Adapter;

use JsonException;
use Legatus\Support\Crypto\Cipher\Cipher;

/**
 * Class BaseAdapter.
 *
 * This base adapter provides methods to easily encrypt and decrypt the contents
 * of a Session.
 */
abstract class BaseAdapter implements StorageAdapter
{
    /**
     * @var Cipher
     */
    private Cipher $cipher;

    /**
     * BaseAdapter constructor.
     *
     * @param Cipher $cipher
     */
    public function __construct(Cipher $cipher)
    {
        $this->cipher = $cipher;
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws JsonException
     */
    protected function encrypt(array $data): string
    {
        $serialized = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->cipher->encrypt($serialized);
    }

    /**
     * @param string $contents
     *
     * @return array
     *
     * @throws JsonException
     * @throws \Legatus\Support\Crypto\Cipher\ExpiredCipher
     * @throws \Legatus\Support\Crypto\Cipher\InvalidCipher
     */
    protected function decrypt(string $contents): array
    {
        $plainText = $this->cipher->decrypt($contents);

        return json_decode($plainText, true, 512, JSON_THROW_ON_ERROR);
    }
}
