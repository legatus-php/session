<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http;

use Legatus\Support\Cipher;

/**
 * Class FilesystemSessionStorage.
 */
final class FilesystemSessionStorage extends BaseSessionStorage
{
    private string $path;

    /**
     * FilesystemStorage constructor.
     *
     * @param Cipher      $cipher
     * @param string|null $path
     */
    public function __construct(Cipher $cipher, string $path = null)
    {
        parent::__construct($cipher);
        $this->path = $path ?? sys_get_temp_dir();
        $this->ensurePath();
    }

    /**
     * @param string $id
     * @param array  $data
     *
     * @throws \JsonException
     */
    public function store(string $id, array $data): void
    {
        $filename = $this->filename($id);
        file_put_contents($filename, $this->encrypt($data));
    }

    /**
     * @param string $id
     */
    public function remove(string $id): void
    {
        $filename = $this->filename($id);
        if (is_file($filename)) {
            unlink($filename);
        }
    }

    /**
     * @param string $id
     *
     * @return array|null
     *
     * @throws \JsonException
     * @throws \Legatus\Support\ExpiredCipher
     * @throws \Legatus\Support\InvalidCipher
     */
    public function retrieve(string $id): ?array
    {
        $filename = $this->filename($id);
        if (!is_file($filename)) {
            return null;
        }
        $contents = file_get_contents($filename);

        return $this->decrypt($contents);
    }

    /**
     * @param string $id
     */
    public function delete(string $id): void
    {
        $filename = $this->filename($id);
        unlink($filename);
    }

    /**
     * The filename is the session id.
     *
     * @param string $id
     *
     * @return string
     */
    protected function filename(string $id): string
    {
        $id = hash('sha256', $id);

        return $this->path.DIRECTORY_SEPARATOR.$id;
    }

    private function ensurePath(): void
    {
        if (!is_dir($this->path) && !mkdir($this->path, 0777, true) && !is_dir($this->path)) {
            throw new \RuntimeException(sprintf('Could not create directory %s', $this->path));
        }
    }
}
