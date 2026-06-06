<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function __construct(
        private OpenRouterService $openRouter
    ) {}

    public function index(Request $request): Response
{
    $user = $request->user();

    $conversations = $user->conversations()
        ->orderByDesc('updated_at')
        ->get(['id', 'title', 'model', 'updated_at', 'user_id']);

    $sharedConversations = $user->sharedConversations()
        ->orderByDesc('updated_at')
        ->get(['conversations.id', 'conversations.title', 'conversations.model', 'conversations.updated_at', 'conversations.user_id']);

    return Inertia::render('Chat/Index', [
        'conversations'       => $conversations,
        'sharedConversations' => $sharedConversations,
        'models'              => $this->openRouter->getAvailableModels(),
    ]);
}
    public function store(Request $request)
{
    $request->validate([
        'model' => 'required|string',
    ]);

    $conversation = $request->user()->conversations()->create([
        'title' => 'Nouvelle conversation',
        'model' => $request->model,
        'agent_id' => $request->agent_id ?? null,
    ]);

    return redirect()->route('conversations.show', $conversation);
}

    public function show(Request $request, Conversation $conversation): Response
{
    $this->authorize('view', $conversation);

    $conversation->load('messages.files');

    $user = $request->user();

    $conversations = $user->conversations()
        ->orderByDesc('updated_at')
        ->get(['id', 'title', 'model', 'updated_at', 'user_id']);

    $sharedConversations = $user->sharedConversations()
        ->orderByDesc('updated_at')
        ->get(['conversations.id', 'conversations.title', 'conversations.model', 'conversations.updated_at', 'conversations.user_id']);

    return Inertia::render('Chat/Show', [
        'conversation'        => $conversation,
        'conversations'       => $conversations,
        'sharedConversations' => $sharedConversations,
        'models'              => $this->openRouter->getAvailableModels(),
    ]);
}

    public function update(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $request->validate([
            'model' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
        ]);

        $conversation->update($request->only(['model', 'title']));

        return response()->json($conversation);
    }

    public function destroy(Conversation $conversation)
    {
        $this->authorize('delete', $conversation);
        $conversation->delete();
        return redirect()->route('chat.index');
    }
}
