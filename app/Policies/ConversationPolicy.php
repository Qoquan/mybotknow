<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->user_id
            || $conversation->isMember($user);
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->user_id
            || $conversation->isMember($user);
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->user_id;
    }
}
