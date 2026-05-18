<?php

namespace Common\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Symfony\Component\Uid\Uuid;

class UuidType extends GuidType
{
    public function getName(): string
    {
        return 'uuid';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Uuid
    {
        if ($value instanceof Uuid || $value === null) {
            return $value;
        }

        return Uuid::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Uuid) {
            return $value->toRfc4122();
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Uuid::fromString((string) $value)->toRfc4122();
    }
}
