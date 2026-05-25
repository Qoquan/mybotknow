<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Page principale du chat
    Route::get('/chat', [ConversationController::class, 'index'])
        ->name('chat.index');

    // Conversations
    Route::post('/conversations', [ConversationController::class, 'store'])
        ->name('conversations.store');

    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])
        ->name('conversations.show');

    Route::patch('/conversations/{conversation}', [ConversationController::class, 'update'])
        ->name('conversations.update');

    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])
        ->name('conversations.destroy');

    // Messages avec streaming
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])
        ->name('messages.store');
});

require __DIR__.'/settings.php';
