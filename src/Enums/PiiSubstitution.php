<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum PiiSubstitution: string
{
    case EntityType = 'entity_type';
    case Hash = 'hash';
}
