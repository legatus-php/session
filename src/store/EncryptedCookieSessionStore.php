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

namespace Legatus\Http;

use Dflydev\FigCookies\SetCookie;
use InvalidArgumentException;
use Legatus\Support\Cipher;
use Legatus\Support\ExpiredCipher;
use Legatus\Support\InvalidCipher;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class EncryptedCookieSessionStore.
 */
final class EncryptedCookieSessionStore extends CookieSessionStore
{
    private Cipher $cipher;

    /**
     * EncryptedCookieSessionStore constructor.
     *
     * @param Cipher         $cipher
     * @param SetCookie|null $cookie
     */
    public function __construct(Cipher $cipher, SetCookie $cookie = null)
    {
        $this->cipher = $cipher;
        parent::__construct($cookie);
    }

    /**
     * @param Request $request
     * @param string  $cookieValue
     *
     * @return Session
     *
     * @throws SessionStoreError
     */
    public function doRetrieve(Request $request, string $cookieValue): Session
    {
        $ttl = $this->cookie->getMaxAge() > 0 ? $this->cookie->getMaxAge() : null;
        try {
            $decrypted = $this->cipher->decrypt($cookieValue, $ttl);
        } catch (ExpiredCipher | InvalidCipher $e) {
            throw new SessionStoreError('Could not retrieve session: '.$e->getMessage(), 0, $e);
        }
        $array = unserialize($decrypted, [false]);
        try {
            return Session::fromArray($array);
        } catch (InvalidArgumentException $e) {
            throw new SessionStoreError('Could not retrieve session: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param Session  $session
     *
     * @return string
     *
     * @throws SessionStoreError
     */
    public function doStore(Request $request, Response $response, Session $session): string
    {
        $array = $session->toArray();
        if ($session->isNew()) {
            $array['id'] = $this->createId();
        }
        $message = serialize($array);

        return $this->cipher->encrypt($message);
    }
}
