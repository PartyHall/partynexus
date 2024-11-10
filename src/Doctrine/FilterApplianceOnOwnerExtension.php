<?php

namespace App\Doctrine;

use App\Entity\Appliance;

final readonly class FilterApplianceOnOwnerExtension extends AbstractPermissionsFilter
{
    protected function getParamName(): string
    {
        return 'mine';
    }

    protected function getClassName(): string
    {
        return Appliance::class;
    }
}
