<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum PiiSubstitution: string
{
    case EntityName = 'entity_name';
    case Hash = 'hash';

    /**
     * @deprecated Use PiiSubstitution::EntityName instead.
     */
    public const EntityType = self::EntityName;
}
