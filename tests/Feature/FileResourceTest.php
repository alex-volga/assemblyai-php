<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Feature;

use AssemblyAI\AssemblyAIClient;
use AssemblyAI\Config;
use AssemblyAI\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class FileResourceTest extends TestCase
{
    public function testUploadAudioString(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'upload_url' => 'https://cdn.assemblyai.com/upload/abc-123',
        ]);

        $client = new AssemblyAIClient(new Config('test-key'), $mock);
        $url = $client->files()->upload('fake audio bytes');

        $this->assertSame('https://cdn.assemblyai.com/upload/abc-123', $url);

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('https://api.assemblyai.com/v2/upload', (string) $req->getUri());
        $this->assertSame('application/octet-stream', $req->getHeaderLine('Content-Type'));
    }

    public function testUploadAudioResource(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'upload_url' => 'https://cdn.assemblyai.com/upload/res-456',
        ]);

        $stream = fopen('php://memory', 'rb+');
        $this->assertIsResource($stream);
        fwrite($stream, 'audio binary chunk');
        rewind($stream);

        $client = new AssemblyAIClient('test-key', $mock);
        $url = $client->upload($stream);

        fclose($stream);

        $this->assertSame('https://cdn.assemblyai.com/upload/res-456', $url);
    }
}
