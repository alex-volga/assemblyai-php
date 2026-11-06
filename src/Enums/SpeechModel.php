<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum SpeechModel: string
{
    case Universal35Pro = 'universal-3-5-pro';
    case Universal36ProPreview = 'universal-3-6-pro-preview';
    case Universal3Pro = 'universal-3-pro';
    case Universal2 = 'universal-2';
}
