<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationShareController extends Controller
{
    // Afficher la page de partage
    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorize('view', $conversation);

        $conversation->load('members');

        return Inertia::render('Chat/Share', [
            'conversation' => $conversation,
            'members'      => $conversation->members()->with('conversationMembers')->get()->map(function ($user) use ($conversation) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->pivot->role,
                    'joined_at' => $user->pivot->joined_at,
                ];
            }),
        ]);
    }

    // Inviter un utilisateur
    public function invite(Request $request, Conversation $conversation)
{
    $this->authorize('update', $conversation);

    $request->validate([
        'email' => 'required|email',
    ]);

    $email = $request->email;
    $user = User::where('email', $email)->first();

    // Utilisateur non inscrit — envoyer un email d'invitation
    if (!$user) {
        \Mail::to($email)->send(
            new \App\Mail\ConversationInvitation($conversation, $request->user(), $email)
        );
        return back()->with('success', "Un email d'invitation a été envoyé à {$email} !");
    }

    // Vérifier que ce n'est pas le propriétaire
    if ($user->id === $conversation->user_id) {
        return back()->withErrors(['email' => 'Cet utilisateur est déjà le propriétaire.']);
    }

    // Vérifier qu'il n'est pas déjà membre
    if ($conversation->isMember($user)) {
        return back()->withErrors(['email' => 'Cet utilisateur est déjà membre.']);
    }

    $conversation->members()->attach($user->id, [
        'role'      => 'member',
        'joined_at' => now(),
    ]);

    return back()->with('success', "{$user->name} a été invité à la conversation !");
}
    // Retirer un membre
    public function remove(Request $request, Conversation $conversation, User $user)
    {
        $this->authorize('update', $conversation);

        // Ne pas supprimer le propriétaire
        if ($user->id === $conversation->user_id) {
            return back()->withErrors(['error' => 'Impossible de retirer le propriétaire.']);
        }

        $conversation->members()->detach($user->id);

        return back()->with('success', "{$user->name} a été retiré de la conversation.");
    }

    // Rejoindre une conversation partagée
    public function join(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if (!$conversation->isMember($user) && $conversation->user_id !== $user->id) {
            return redirect('/chat')->withErrors(['error' => 'Acces non autorise.']);
        }

        return redirect()->route('conversations.show', $conversation);
    }
}
