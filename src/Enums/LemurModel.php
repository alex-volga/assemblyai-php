<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum LemurModel: string
{
    case Claude35Sonnet = 'anthropic/claude-3-5-sonnet';
    case Claude3Haiku = 'anthropic/claude-3-haiku';
    case Claude3Opus = 'anthropic/claude-3-opus';
    case Claude3Sonnet = 'anthropic/claude-3-sonnet';
    case Default = 'assemblyai/default';
}
