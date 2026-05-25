<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserModelUsage;
use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.openrouter.key');
        $this->baseUrl = config('services.openrouter.url');
    }

    public function getAvailableModels(): array
    {
        return [
            [
                'id'          => 'openai/gpt-4o-mini',
                'name'        => 'GPT-4o Mini',
                'description' => 'Rapide et économique',
                'provider'    => 'OpenAI',
            ],
            [
                'id'          => 'openai/gpt-4o',
                'name'        => 'GPT-4o',
                'description' => 'Très performant',
                'provider'    => 'OpenAI',
            ],
            [
                'id'          => 'anthropic/claude-3.5-sonnet',
                'name'        => 'Claude 3.5 Sonnet',
                'description' => 'Excellent en raisonnement',
                'provider'    => 'Anthropic',
            ],
            [
                'id'          => 'anthropic/claude-3-haiku',
                'name'        => 'Claude 3 Haiku',
                'description' => 'Rapide et léger',
                'provider'    => 'Anthropic',
            ],
            [
                'id'          => 'google/gemini-2.0-flash-001',
                'name'        => 'Gemini 2.0 Flash',
                'description' => 'Multimodal Google',
                'provider'    => 'Google',
            ],
            [
                'id'          => 'meta-llama/llama-3.3-70b-instruct',
                'name'        => 'Llama 3.3 70B',
                'description' => 'Open source puissant',
                'provider'    => 'Meta',
            ],
        ];
    }

    public function buildMessages(Conversation $conversation, ?string $systemPrompt = null): array
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = [
                'role'    => 'system',
                'content' => $systemPrompt,
            ];
        }

        foreach ($conversation->messages()->orderBy('created_at')->get() as $message) {
            $messages[] = [
                'role'    => $message->role,
                'content' => $message->content,
            ];
        }

        return $messages;
    }

    public function stream(Conversation $conversation, string $systemPrompt = null): \Generator
    {
        $messages = $this->buildMessages($conversation, $systemPrompt);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => config('app.name'),
        ])->withOptions([
            'stream' => true,
        ])->post($this->baseUrl . '/chat/completions', [
            'model'  => $conversation->model,
            'messages' => $messages,
            'stream' => true,
        ]);

        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readLine($body);

            if (str_starts_with($line, 'data: ')) {
                $data = substr($line, 6);

                if ($data === '[DONE]') {
                    break;
                }

                $json = json_decode($data, true);

                if (isset($json['choices'][0]['delta']['content'])) {
                    yield $json['choices'][0]['delta']['content'];
                }
            }
        }
    }

    private function readLine($body): string
    {
        $line = '';
        while (!$body->eof()) {
            $char = $body->read(1);
            if ($char === "\n") {
                break;
            }
            $line .= $char;
        }
        return trim($line);
    }

    public function generateTitle(string $firstMessage): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => config('app.name'),
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'openai/gpt-4o-mini',
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'Génère un titre court (4-6 mots maximum) pour une conversation qui commence par ce message. Réponds UNIQUEMENT avec le titre, sans guillemets, sans ponctuation finale.',
                ],
                [
                    'role'    => 'user',
                    'content' => $firstMessage,
                ],
            ],
            'max_tokens' => 20,
        ]);

        return $response->json('choices.0.message.content', 'Nouvelle conversation');
    }

    public function updateUsage(Conversation $conversation, int $tokensUsed): void
    {
    $conversation->increment('total_tokens', $tokensUsed);

    $usage = UserModelUsage::firstOrCreate(
        [
            'user_id'    => $conversation->user_id,
            'model'      => $conversation->model,
            'usage_date' => now()->toDateString(),
        ],
        [
            'total_messages' => 0,
            'total_tokens'   => 0,
        ]
    );

    $usage->increment('total_tokens', $tokensUsed);
    $usage->increment('total_messages');
    }
}
