<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Feature;

use AssemblyAI\AssemblyAIClient;
use AssemblyAI\Enums\LemurModel;
use AssemblyAI\Parameters\LemurActionItemsParams;
use AssemblyAI\Parameters\LemurQnAParams;
use AssemblyAI\Parameters\LemurSummaryParams;
use AssemblyAI\Parameters\LemurTaskParams;
use AssemblyAI\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class LemurResourceTest extends TestCase
{
    public function testLemurTask(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'request_id' => 'req-1',
            'response' => 'Summary of the meeting',
            'usage' => [
                'input_tokens' => 100,
                'output_tokens' => 20,
            ],
        ]);

        $client = new AssemblyAIClient('key', $mock);
        $res = $client->lemur()->task(new LemurTaskParams(
            transcriptIds: ['tx-1', 'tx-2'],
            prompt: 'Summarize the transcript',
            finalModel: LemurModel::Claude35Sonnet,
        ));

        $this->assertSame('req-1', $res->requestId);
        $this->assertSame('Summary of the meeting', $res->response);
        $this->assertNotNull($res->usage);
        $this->assertSame(100, $res->usage->inputTokens);

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('https://api.assemblyai.com/v2/lemur/task', (string) $req->getUri());
    }

    public function testLemurSummary(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'request_id' => 'req-2',
            'response' => 'Summary in bullets',
        ]);

        $client = new AssemblyAIClient('key', $mock);
        $res = $client->lemur()->summary(new LemurSummaryParams(
            transcriptIds: ['tx-1'],
            answerFormat: 'bullet points',
        ));

        $this->assertSame('req-2', $res->requestId);
        $this->assertSame('Summary in bullets', $res->response);
    }

    public function testLemurQuestionAnswer(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'request_id' => 'req-3',
            'response' => '[{"question": "Who was speaking?", "answer": "Alex"}]',
        ]);

        $client = new AssemblyAIClient('key', $mock);
        $res = $client->lemur()->questionAnswer(new LemurQnAParams(
            transcriptIds: ['tx-1'],
            questions: [
                ['question' => 'Who was speaking?'],
            ],
        ));

        $this->assertSame('req-3', $res->requestId);
    }

    public function testLemurActionItems(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'request_id' => 'req-4',
            'response' => '1. Write tests 2. Deploy',
        ]);

        $client = new AssemblyAIClient('key', $mock);
        $res = $client->lemur()->actionItems(new LemurActionItemsParams(
            transcriptIds: ['tx-1'],
        ));

        $this->assertSame('req-4', $res->requestId);
        $this->assertSame('1. Write tests 2. Deploy', $res->response);
    }

    public function testGetResponseAndPurge(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'request_id' => 'req-5',
            'response' => 'Saved response',
        ]);
        $mock->addJsonResponse(200, ['status' => 'deleted']);

        $client = new AssemblyAIClient('key', $mock);

        $get = $client->lemur()->getResponse('req-5');
        $this->assertSame('Saved response', $get->response);

        $purged = $client->lemur()->purgeData('req-5');
        $this->assertTrue($purged);
    }
}
