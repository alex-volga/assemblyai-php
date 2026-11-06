<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum SummaryType: string
{
    case Bullets = 'bullets';
    case Paragraph = 'paragraph';

    /** @deprecated Deprecated by AssemblyAI; automatically maps to 'bullets' in Speech Understanding. */
    case BulletsVerbose = 'bullets_verbose';

    /** @deprecated Deprecated by AssemblyAI; automatically maps to 'paragraph' in Speech Understanding. */
    case Gist = 'gist';

    /** @deprecated Deprecated by AssemblyAI; automatically maps to 'paragraph' in Speech Understanding. */
    case Headline = 'headline';
}
