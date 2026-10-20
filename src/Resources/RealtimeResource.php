<?php

declare(strict_types=1);

namespace AssemblyAI\Resources;

use AssemblyAI\Exceptions\ClientException;

final class RealtimeResource extends AbstractResource
{
    public const string V3_TOKEN_URL = 'https://streaming.assemblyai.com/v3/token';

    /**
     * Create a temporary authentication token for real-time streaming WebSocket connections.
     * This avoids exposing your permanent API key to browser or mobile clients.
     *
     * @param int $expiresInSeconds Time window to open the WebSocket (1 to 600 seconds)
     * @param int|null $maxSessionDurationSeconds Maximum streaming session length in seconds (60 to 10800)
     * @return string Temporary authentication token
     */
    public function createTemporaryToken(int $expiresInSeconds = 480, ?int $maxSessionDurationSeconds = null): string
    {
        if ($expiresInSeconds < 1 || $expiresInSeconds > 600) {
            throw new ClientException('expires_in_seconds must be between 1 and 600.');
        }

        $queryParams = [
            'expires_in_seconds' => $expiresInSeconds,
        ];

        if ($maxSessionDurationSeconds !== null) {
            if ($maxSessionDurationSeconds < 60 || $maxSessionDurationSeconds > 10800) {
                throw new ClientException('max_session_duration_seconds must be between 60 and 10800.');
            }
            $queryParams['max_session_duration_seconds'] = $maxSessionDurationSeconds;
        }

        $response = $this->http->get(self::V3_TOKEN_URL, $queryParams);
        $json = $response->json();

        if (!isset($json['token']) || !is_string($json['token'])) {
            throw new ClientException('Failed to retrieve streaming token from response.');
        }

        return $json['token'];
    }
}
