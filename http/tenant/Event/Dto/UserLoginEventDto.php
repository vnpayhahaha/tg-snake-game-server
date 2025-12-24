<?php

namespace http\tenant\Event\Dto;

/**
 * @template T
 */
final class UserLoginEventDto
{
    /**
     * @param T $user
     */
    public function __construct(
        private readonly object $user,
        private readonly string $tenant_id,
        private readonly string $ip,
        private readonly string $os,
        private readonly string $browser,
        private readonly bool $isLogin = true,
    )
    {
    }

    /**
     * @return T
     */
    public function getUser(): object
    {
        return $this->user;
    }

    public function isLogin(): bool
    {
        return $this->isLogin;
    }

    public function getTenantId(): string
    {
        return $this->tenant_id;
    }
    public function getIp(): string
    {
        return $this->ip;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getOs(): string
    {
        return $this->os;
    }
}
