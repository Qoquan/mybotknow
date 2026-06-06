<?php

namespace App\Mail;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $inviter,
        public string $inviteeEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚔️ {$this->inviter->name} t'invite à rejoindre une quête sur QuestMaster !",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.conversation-invitation',
        );
    }
}
