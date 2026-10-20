<?php

declare(strict_types=1);

namespace AssemblyAI\Resources;

use AssemblyAI\Exceptions\ClientException;
use Psr\Http\Message\StreamInterface;

final class FileResource extends AbstractResource
{
    /**
     * Upload an audio file or stream to AssemblyAI servers.
     * Returns the uploaded media URL which can be passed to transcript requests.
     *
     * @param string|resource|StreamInterface $file Local file path, open stream resource, or StreamInterface
     * @return string The upload URL
     */
    public function upload(mixed $file): string
    {
        $response = $this->http->upload('/v2/upload', $file);
        $json = $response->json();

        if (!isset($json['upload_url']) || !is_string($json['upload_url'])) {
            throw new ClientException('Unexpected upload response from AssemblyAI: missing upload_url.');
        }

        return $json['upload_url'];
    }
}
