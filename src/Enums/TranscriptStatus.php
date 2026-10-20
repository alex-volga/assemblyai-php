<?php

declare(strict_types=1);

namespace AssemblyAI\Enums;

enum TranscriptStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Error = 'error';

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public function isProcessing(): bool
    {
        return $this === self::Processing;
    }

    public function isQueued(): bool
    {
        return $this === self::Queued;
    }

    public function isError(): bool
    {
        return $this === self::Error;
    }

    public function isFinished(): bool
    {
        return $this === self::Completed || $this === self::Error;
    }
}
