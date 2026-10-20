<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum SpeechModel: string
{
    case Best = 'best';
    case Nano = 'nano';
    case Universal2 = 'universal-2';
    case Universal3Pro = 'universal-3-pro';
}
