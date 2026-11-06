<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

/**
 * @deprecated Deprecated by AssemblyAI in favor of the LLM-powered Speech Understanding architecture.
 */
enum SummaryModel: string
{
    case Informative = 'informative';
    case Conversational = 'conversational';
    case Catchy = 'catchy';
}
