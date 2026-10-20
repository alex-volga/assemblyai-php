<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Unit;

use AssemblyAI\Config;
use AssemblyAI\Exceptions\AuthenticationException;
use AssemblyAI\Exceptions\NotFoundException;
use AssemblyAI\Exceptions\RateLimitException;
use AssemblyAI\Exceptions\ServerException;
use AssemblyAI\Exceptions\ValidationException;
use AssemblyAI\Http\HttpClient;
use AssemblyAI\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class HttpClientTest extends TestCase
{
    public function testAuthorizationHeaderSent(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, ['status' => 'ok']);

        $config = new Config(apiKey: 'secret_123');
        $http = new HttpClient($config, $mock);

        $response = $http->get('/test');

        $this->assertSame(200, $response->status());
        $this->assertCount(1, $mock->requests);
        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertSame('secret_123', $req->getHeaderLine('Authorization'));
        $this->assertStringContainsString('AssemblyAI-PHP-SDK', $req->getHeaderLine('User-Agent'));
        $this->assertSame('https://api.assemblyai.com/test', (string) $req->getUri());
    }

    public function testAuthenticationExceptionOn401(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(401, ['error' => 'Invalid API key provided.']);

        $config = new Config(apiKey: 'invalid');
        $http = new HttpClient($config, $mock);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key provided.');

        $http->get('/v2/transcript');
    }

    public function testNotFoundExceptionOn404(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(404, ['error' => 'Transcript not found']);

        $config = new Config(apiKey: 'key');
        $http = new HttpClient($config, $mock);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Transcript not found');

        $http->get('/v2/transcript/non-existent');
    }

    public function testValidationExceptionOn400(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(400, ['error' => 'audio_url is invalid']);

        $config = new Config(apiKey: 'key');
        $http = new HttpClient($config, $mock);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('audio_url is invalid');

        $http->post('/v2/transcript', ['audio_url' => 'bad']);
    }

    public function testRateLimitExceptionOn429(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(429, ['error' => 'Rate limit exceeded']);

        $config = new Config(apiKey: 'key');
        $http = new HttpClient($config, $mock);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $http->get('/v2/transcript');
    }

    public function testServerExceptionOn500(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(500, ['error' => 'Internal server error']);

        $config = new Config(apiKey: 'key');
        $http = new HttpClient($config, $mock);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Internal server error');

        $http->get('/v2/transcript');
    }
}
