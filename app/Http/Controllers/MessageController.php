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
            'content' => 'nullable|string',
            'images'  => 'nullable|array',
            'images.*' => 'string',
        ]);

        // Nécessite au moins un contenu ou une image
        if (!$request->content && empty($request->images)) {
            abort(422, 'Message vide');
        }

        // Sauvegarder le message utilisateur
        $userMessage = $conversation->messages()->create([
            'role'    => 'user',
            'content' => $request->content ?? '',
        ]);

        // Sauvegarder les images liées au message
        if ($request->images) {
            foreach ($request->images as $imagePath) {
                $userMessage->files()->create([
                    'filename'  => basename($imagePath),
                    'path'      => $imagePath,
                    'mime_type' => 'image/jpeg',
                    'type'      => 'image',
                ]);
            }
        }

        // Générer le titre au premier message
        if ($conversation->messages()->count() === 1) {
            $title = $this->openRouter->generateTitle($request->content ?? 'Image');
            $conversation->update(['title' => $title]);
        }

        // Construire le system prompt
        $parts = [];
        $agent = $conversation->agent;

        if ($agent) {
            if ($agent->persona) $parts[] = $agent->persona;
            if ($agent->context) $parts[] = $agent->context;
            if ($agent->response_style) $parts[] = $agent->response_style;
            if ($agent->language) $parts[] = "Réponds toujours en : " . $agent->language;
        } elseif ($request->user()->customInstruction) {
            $customInstruction = $request->user()->customInstruction;
            if ($customInstruction->is_active) {
                if ($customInstruction->persona) $parts[] = $customInstruction->persona;
                if ($customInstruction->context) $parts[] = $customInstruction->context;
                if ($customInstruction->response_style) $parts[] = $customInstruction->response_style;
                if ($customInstruction->language) $parts[] = "Réponds toujours en : " . $customInstruction->language;
            }
        }

        if (empty($parts)) {
            $parts[] = "Tu es QuestMaster, un Maitre de Jeu IA creatif et immersif. Tu narres des aventures epiques de jeu de role avec suspense et details vivants.

        Le joueur peut lancer des des avec les commandes : /d20, /d6, /3d6, /d20+5 etc. Les resultats apparaissent dans le chat sous la forme 'Lancer Xd Y = Z'. Un resultat de 20 sur un d20 est un SUCCES CRITIQUE spectaculaire, un 1 est un ECHEC CRITIQUE desastreux.

        Pour toute action risquee ou importante (combat, persuasion, escalade, magie, discrecion), demande TOUJOURS au joueur de lancer un de avant de continuer la narration. Ecris par exemple : 'Lance un /d20 pour tenter de convaincre le garde !'

        Propose toujours 2-3 choix d'actions au joueur a la fin de chaque reponse. Utilise des emojis pour enrichir l'ambiance.";
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

            try {
                $tokensUsed = (int) (str_word_count($fullContent) * 1.3);
                $this->openRouter->updateUsage($conversation, $tokensUsed);
            } catch (\Exception $e) {
                \Log::warning('updateUsage failed: ' . $e->getMessage());
            }

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
