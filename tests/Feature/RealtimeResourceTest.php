<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Feature;

use AssemblyAI\AssemblyAIClient;
use AssemblyAI\Exceptions\ClientException;
use AssemblyAI\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class RealtimeResourceTest extends TestCase
{
    public function testCreateTemporaryToken(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'token' => 'temp-stream-token-xyz',
        ]);

        $client = new AssemblyAIClient('key', $mock);
        $token = $client->realtime()->createTemporaryToken(expiresInSeconds: 60, maxSessionDurationSeconds: 1800);

        $this->assertSame('temp-stream-token-xyz', $token);

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertSame('GET', $req->getMethod());
        $this->assertStringContainsString('streaming.assemblyai.com/v3/token', (string) $req->getUri());
        $this->assertStringContainsString('expires_in_seconds=60', (string) $req->getUri());
        $this->assertStringContainsString('max_session_duration_seconds=1800', (string) $req->getUri());
    }

    public function testCreateTemporaryTokenValidatesExpiresIn(): void
    {
        $mock = new MockHttpClient();
        $client = new AssemblyAIClient('key', $mock);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('expires_in_seconds must be between 1 and 600.');

        $client->realtime()->createTemporaryToken(expiresInSeconds: 0);
    }
}
