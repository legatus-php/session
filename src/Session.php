<?php

declare(strict_types=1);

/*
 * This file is part of the Legatus project organization.
 * (c) Matías Navarro-Carter <contact@mnavarro.dev>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Legatus\Http\Session;

use Cake\Chronos\ChronosInterface;

/**
 * Interface Session.
 *
 * Models an HTTP session.
 *
 * Also contains common attributes names that are injected in the user session.
 */
interface Session
{
    public const ATTR_ACCOUNT_ID = 'account-id';
    public const ATTR_USER_AGENT = 'user-agent';
    public const ATTR_IP_ADDRESS = 'ip-address';
    public const ATTR_CSRF_TOKEN = 'csrf-token';
    public const ATTR_F_MESSAGES = 'f-messages';

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * Mutates the session data.
     *
     * @param callable $mutator
     *
     * @return Session
     */
    public function mutate(callable $mutator): Session;

    /**
     * @param string $attr
     * @param null   $default
     *
     * @return mixed|null
     */
    public function get(string $attr, $default = null);

    /**
     * @param string $attr
     *
     * @return bool
     */
    public function has(string $attr): bool;

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param string $attr
     */
    public function remove(string $attr): void;

    /**
     * Regenerates the session giving it a new id, preserving the old data.
     *
     * This method MUST be called upon any authentication event (login, logout)
     *
     * This method SHOULD be called frequently.
     */
    public function regenerate(): void;

    public function destroy(): void;

    /**
     * @return bool
     */
    public function isDestroyed(): bool;

    /**
     * @return ChronosInterface
     */
    public function lastModified(): ChronosInterface;

    /**
     * @return ChronosInterface
     */
    public function startedAt(): ChronosInterface;

    /**
     * @param int $ttl
     *
     * @return bool
     */
    public function isExpired(int $ttl): bool;
}
