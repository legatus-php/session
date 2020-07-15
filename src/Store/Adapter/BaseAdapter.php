<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session\Store\Adapter;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use JsonException;

/**
 * Class BaseAdapter.
 *
 * This base adapter provides methods to easily encrypt and decrypt the contents
 * of a Session.
 */
abstract class BaseAdapter implements StorageAdapter
{
    private Key $key;

    /**
     * BaseAdapter constructor.
     *
     * @param Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @throws JsonException
     * @throws EnvironmentIsBrokenException
     */
    protected function encrypt(array $data): string
    {
        $serialized = json_encode($data, JSON_THROW_ON_ERROR);

        return Crypto::encrypt($serialized, $this->key, true);
    }

    /**
     * @param string $contents
     *
     * @return array
     *
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws JsonException
     */
    protected function decrypt(string $contents): array
    {
        $plainText = Crypto::decrypt($contents, $this->key, true);

        return json_decode($plainText, true, 512, JSON_THROW_ON_ERROR);
    }
}
