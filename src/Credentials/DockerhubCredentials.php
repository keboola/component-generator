<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton\Credentials;

class DockerhubCredentials
{
    private ?string $user;

    private ?string $password;

    public function __construct(?string $user = null, ?string $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
