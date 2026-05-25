<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomInstructionController extends Controller
{
    public function show(Request $request): Response
    {
        $instruction = $request->user()->customInstruction;

        return Inertia::render('Chat/Instructions', [
            'instruction' => $instruction,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'persona'        => 'nullable|string|max:1000',
            'context'        => 'nullable|string|max:1000',
            'response_style' => 'nullable|string|max:1000',
            'language'       => 'nullable|string|max:10',
            'is_active'      => 'boolean',
        ]);

        $request->user()->customInstruction()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only(['persona', 'context', 'response_style', 'language', 'is_active'])
        );

        return back()->with('success', 'Instructions sauvegardées !');
    }
}
