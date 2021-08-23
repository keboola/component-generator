<?php

declare(strict_types=1);

namespace Keboola\AppSkeleton;

class DeveloperPortalCredentials
{
    private string $vendorId;

    private string $componentId;

    private string $serviceAccountName;

    private string $serviceAccountPassword;

    public function __construct(
        string $vendorId,
        string $componentId,
        string $serviceAccountName,
        string $serviceAccountPassword
    ) {
        $this->vendorId = $vendorId;
        $this->componentId = $componentId;
        $this->serviceAccountName = $serviceAccountName;
        $this->serviceAccountPassword = $serviceAccountPassword;
    }

    public function getVendorId(): string
    {
        return $this->vendorId;
    }

    public function getComponentId(): string
    {
        return $this->componentId;
    }

    public function getServiceAccountName(): string
    {
        return $this->serviceAccountName;
    }

    public function getServiceAccountPassword(): string
    {
        return $this->serviceAccountPassword;
    }
}
