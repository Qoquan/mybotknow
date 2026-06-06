<?php

namespace App\Services;

use App\Models\Conversation;
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
                'description' => 'Rapide et economique',
                'provider'    => 'OpenAI',
            ],
            [
                'id'          => 'openai/gpt-4o',
                'name'        => 'GPT-4o',
                'description' => 'Tres performant',
                'provider'    => 'OpenAI',
            ],
            [
                'id'          => 'openai/o4-mini',
                'name'        => 'O4 Mini',
                'description' => 'Raisonnement avance',
                'provider'    => 'OpenAI',
            ],
            [
                'id'          => 'anthropic/claude-3-haiku',
                'name'        => 'Claude 3 Haiku',
                'description' => 'Rapide et leger',
                'provider'    => 'Anthropic',
            ],
            [
                'id'          => 'anthropic/claude-3-5-sonnet',
                'name'        => 'Claude 3.5 Sonnet',
                'description' => 'Excellent en raisonnement',
                'provider'    => 'Anthropic',
            ],
            [
                'id'          => 'google/gemini-2.0-flash-001',
                'name'        => 'Gemini 2.0 Flash',
                'description' => 'Multimodal Google',
                'provider'    => 'Google',
            ],
            [
                'id'          => 'google/gemini-2.5-pro-preview-06-05',
                'name'        => 'Gemini 2.5 Pro',
                'description' => 'Tres puissant Google',
                'provider'    => 'Google',
            ],
            [
                'id'          => 'meta-llama/llama-3.3-70b-instruct',
                'name'        => 'Llama 3.3 70B',
                'description' => 'Open source puissant',
                'provider'    => 'Meta',
            ],
            [
                'id'          => 'deepseek/deepseek-chat',
                'name'        => 'DeepSeek V3',
                'description' => 'Tres performant',
                'provider'    => 'DeepSeek',
            ],
            [
                'id'          => 'mistralai/mistral-large',
                'name'        => 'Mistral Large',
                'description' => 'Europeen performant',
                'provider'    => 'Mistral',
            ],
            [
                'id'          => 'gryphe/mythomax-l2-13b',
                'name'        => 'MythoMax 13B',
                'description' => 'Ideal pour le JDR',
                'provider'    => 'Gryphe',
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

    foreach ($conversation->messages()->with('files')->orderBy('created_at')->get() as $message) {
        // Message sans images
        if ($message->files->isEmpty()) {
            $messages[] = [
                'role'    => $message->role,
                'content' => $message->content,
            ];
            continue;
        }

        // Message avec images (format multimodal)
        $content = [];

        if ($message->content) {
            $content[] = [
                'type' => 'text',
                'text' => $message->content,
            ];
        }

        foreach ($message->files as $file) {
            $fullPath = storage_path('app/public/' . $file->path);
            if (file_exists($fullPath)) {
                $imageData = base64_encode(file_get_contents($fullPath));
                $mimeType  = $file->mime_type;
                $content[] = [
                    'type'       => 'image_url',
                    'image_url'  => [
                        'url' => "data:{$mimeType};base64,{$imageData}",
                    ],
                ];
            }
        }

        $messages[] = [
            'role'    => $message->role,
            'content' => $content,
        ];
    }

    return $messages;
}
    public function stream(Conversation $conversation, ?string $systemPrompt = null): \Generator
    {
        $messages = $this->buildMessages($conversation, $systemPrompt);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => config('app.name'),
        ])->withOptions([
            'stream'  => true,
            'timeout' => 120,
        ])->post($this->baseUrl . '/chat/completions', [
            'model'    => $conversation->model,
            'messages' => $messages,
            'stream'   => true,
        ]);

        $body   = $response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $chunk   = $body->read(1024);
            $buffer .= $chunk;
            $lines   = explode("\n", $buffer);
            $buffer  = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);

                    if ($data === '[DONE]') {
                        return;
                    }

                    $json = json_decode($data, true);

                    if (isset($json['choices'][0]['delta']['content'])) {
                        yield $json['choices'][0]['delta']['content'];
                    }
                }
            }
        }
    }

    public function generateTitle(string $firstMessage): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer'  => config('app.url'),
                'X-Title'       => config('app.name'),
            ])->post($this->baseUrl . '/chat/completions', [
                'model'      => 'openai/gpt-4o-mini',
                'max_tokens' => 20,
                'messages'   => [
                    [
                        'role'    => 'system',
                        'content' => 'Genere un titre court (4-6 mots maximum) pour une conversation qui commence par ce message. Reponds UNIQUEMENT avec le titre, sans guillemets, sans ponctuation finale.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $firstMessage,
                    ],
                ],
            ]);

            return $response->json('choices.0.message.content', 'Nouvelle quete');
        } catch (\Exception $e) {
            return 'Nouvelle quete';
        }
    }

    public function updateUsage(Conversation $conversation, int $tokensUsed): void
    {
        try {
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
        } catch (\Exception $e) {
            \Log::warning('updateUsage failed: ' . $e->getMessage());
        }
    }
}
