<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum SummaryModel: string
{
    case Informative = 'informative';
    case Conversational = 'conversational';
    case Catchy = 'catchy';
}
