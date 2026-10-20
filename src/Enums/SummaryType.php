<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum SummaryType: string
{
    case Bullets = 'bullets';
    case BulletsVerbose = 'bullets_verbose';
    case Gist = 'gist';
    case Headline = 'headline';
    case Paragraph = 'paragraph';
}
