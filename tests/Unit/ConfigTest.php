<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Unit;

use AssemblyAI\Config;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $config = new Config(apiKey: 'test-api-key');

        $this->assertSame('test-api-key', $config->apiKey);
        $this->assertSame(Config::DEFAULT_BASE_URL, $config->baseUrl);
        $this->assertSame(60.0, $config->timeout);
        $this->assertSame(3, $config->pollInterval);
        $this->assertSame(300, $config->pollTimeout);
        $this->assertSame([], $config->additionalHeaders);
    }

    public function testEuConfig(): void
    {
        $config = Config::forEu(apiKey: 'eu-key');

        $this->assertSame('eu-key', $config->apiKey);
        $this->assertSame(Config::EU_BASE_URL, $config->baseUrl);
    }

    public function testEmptyApiKeyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AssemblyAI API key cannot be empty.');

        new Config(apiKey: '   ');
    }

    public function testInvalidBaseUrlThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base URL');

        new Config(apiKey: 'test', baseUrl: 'invalid-url');
    }

    public function testInvalidTimeoutThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be greater than zero.');

        new Config(apiKey: 'test', timeout: 0);
    }

    public function testInvalidPollIntervalThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Poll interval must be greater than zero.');

        new Config(apiKey: 'test', pollInterval: 0);
    }
}
