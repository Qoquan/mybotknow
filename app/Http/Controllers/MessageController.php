<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    public function __construct(
        private OpenRouterService $openRouter
    ) {}

    public function store(Request $request, Conversation $conversation): StreamedResponse
    {
        $this->authorize('update', $conversation);

        $request->validate([
            'content' => 'required|string',
        ]);

        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $request->content,
        ]);

        if ($conversation->messages()->count() === 1) {
            $title = $this->openRouter->generateTitle($request->content);
            $conversation->update(['title' => $title]);
        }

        $systemPrompt = null;
        $parts = [];

        // Priorité 1 : Agent lié à la conversation
        $agent = $conversation->agent;

        if ($agent) {
            if ($agent->persona) $parts[] = $agent->persona;
            if ($agent->context) $parts[] = $agent->context;
            if ($agent->response_style) $parts[] = $agent->response_style;
            if ($agent->language) $parts[] = "Réponds toujours en : " . $agent->language;
        }
        // Priorité 2 : Instructions personnalisées globales
        elseif ($request->user()->customInstruction) {
            $customInstruction = $request->user()->customInstruction;
            if ($customInstruction->is_active) {
                if ($customInstruction->persona) $parts[] = $customInstruction->persona;
                if ($customInstruction->context) $parts[] = $customInstruction->context;
                if ($customInstruction->response_style) $parts[] = $customInstruction->response_style;
                if ($customInstruction->language) $parts[] = "Réponds toujours en : " . $customInstruction->language;
            }
        }

        // Instruction de base QuestMaster si aucune instruction définie
        if (empty($parts)) {
            $parts[] = "Tu es QuestMaster, un Maître de Jeu IA créatif et immersif. Tu narres des aventures épiques de jeu de rôle avec suspense et détails. Tu proposes toujours 2-3 choix d'actions au joueur à la fin de chaque réponse. Utilise des emojis pour enrichir l'ambiance (⚔️🐉🏰🗡️🔮🎲).";
        }

        $systemPrompt = implode("\n\n", $parts);

        return response()->stream(function () use ($conversation, $systemPrompt) {
            $fullContent = '';

            foreach ($this->openRouter->stream($conversation, $systemPrompt) as $chunk) {
                $fullContent .= $chunk;
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }

            $message = $conversation->messages()->create([
                'role'       => 'assistant',
                'content'    => $fullContent,
                'model_used' => $conversation->model,
            ]);

            $tokensUsed = (int) (str_word_count($fullContent) * 1.3);
            $this->openRouter->updateUsage($conversation, $tokensUsed);

            echo "data: " . json_encode([
                'done'       => true,
                'message_id' => $message->id,
                'title'      => $conversation->fresh()->title,
            ]) . "\n\n";

            ob_flush();
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
