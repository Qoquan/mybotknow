<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationShareController;
use App\Http\Controllers\CustomInstructionController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Chat
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

    // Partage de conversations
    Route::get('/conversations/{conversation}/share', [ConversationShareController::class, 'show'])
        ->name('conversations.share');

    Route::post('/conversations/{conversation}/invite', [ConversationShareController::class, 'invite'])
        ->name('conversations.invite');

    Route::delete('/conversations/{conversation}/members/{user}', [ConversationShareController::class, 'remove'])
        ->name('conversations.members.remove');

    // Instructions personnalisées
    Route::get('/instructions', [CustomInstructionController::class, 'show'])
        ->name('instructions.show');

    Route::post('/instructions', [CustomInstructionController::class, 'update'])
        ->name('instructions.update');

    // Agents
    Route::get('/agents', [AgentController::class, 'index'])
        ->name('agents.index');

    Route::post('/agents', [AgentController::class, 'store'])
        ->name('agents.store');

    Route::patch('/agents/{agent}', [AgentController::class, 'update'])
        ->name('agents.update');

    Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])
        ->name('agents.destroy');

    // Upload image
    Route::post('/upload/image', [ImageController::class, 'upload'])
        ->name('image.upload');

});

require __DIR__.'/settings.php';
