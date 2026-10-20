<?php

declare(strict_types=1);

namespace AssemblyAI\Resources;

use AssemblyAI\Exceptions\ClientException;
use AssemblyAI\Parameters\LemurActionItemsParams;
use AssemblyAI\Parameters\LemurQnAParams;
use AssemblyAI\Parameters\LemurSummaryParams;
use AssemblyAI\Parameters\LemurTaskParams;
use AssemblyAI\ValueObjects\LemurResponse;

final class LemurResource extends AbstractResource
{
    /**
     * Run a custom LLM prompt over one or more transcripts.
     *
     * @param LemurTaskParams|array<string, mixed> $params
     */
    public function task(LemurTaskParams|array $params): LemurResponse
    {
        $payload = $params instanceof LemurTaskParams ? $params->toArray() : $params;

        if (!isset($payload['prompt']) || !is_string($payload['prompt']) || trim($payload['prompt']) === '') {
            throw new ClientException('Prompt is required for LeMUR task.');
        }

        $response = $this->http->post('/v2/lemur/task', $payload);

        return LemurResponse::fromArray($response->json());
    }

    /**
     * Generate a summary over one or more transcripts using LeMUR.
     *
     * @param LemurSummaryParams|array<string, mixed> $params
     */
    public function summary(LemurSummaryParams|array $params): LemurResponse
    {
        $payload = $params instanceof LemurSummaryParams ? $params->toArray() : $params;

        $response = $this->http->post('/v2/lemur/summary', $payload);

        return LemurResponse::fromArray($response->json());
    }

    /**
     * Ask questions and get answers from one or more transcripts using LeMUR.
     *
     * @param LemurQnAParams|array<string, mixed> $params
     */
    public function questionAnswer(LemurQnAParams|array $params): LemurResponse
    {
        $payload = $params instanceof LemurQnAParams ? $params->toArray() : $params;

        if (!isset($payload['questions']) || !is_array($payload['questions']) || $payload['questions'] === []) {
            throw new ClientException('At least one question is required for LeMUR Question & Answer.');
        }

        $response = $this->http->post('/v2/lemur/question-answer', $payload);

        return LemurResponse::fromArray($response->json());
    }

    /**
     * Extract action items from one or more transcripts using LeMUR.
     *
     * @param LemurActionItemsParams|array<string, mixed> $params
     */
    public function actionItems(LemurActionItemsParams|array $params): LemurResponse
    {
        $payload = $params instanceof LemurActionItemsParams ? $params->toArray() : $params;

        $response = $this->http->post('/v2/lemur/action-items', $payload);

        return LemurResponse::fromArray($response->json());
    }

    /**
     * Retrieve the result of a previously submitted asynchronous LeMUR request.
     */
    public function getResponse(string $taskId): LemurResponse
    {
        $taskId = trim($taskId);
        if ($taskId === '') {
            throw new ClientException('Task ID cannot be empty.');
        }

        $response = $this->http->get('/v2/lemur/' . rawurlencode($taskId));

        return LemurResponse::fromArray($response->json());
    }

    /**
     * Purge the LLM response data associated with a specific LeMUR request.
     */
    public function purgeData(string $taskId): bool
    {
        $taskId = trim($taskId);
        if ($taskId === '') {
            throw new ClientException('Task ID cannot be empty.');
        }

        $response = $this->http->delete('/v2/lemur/' . rawurlencode($taskId));

        return $response->isSuccessful();
    }
}
