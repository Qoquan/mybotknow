<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    public function index(Request $request): Response
    {
        $agents = $request->user()
            ->agents()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return Inertia::render('Chat/Agents', [
            'agents' => $agents,
            'models' => app(\App\Services\OpenRouterService::class)->getAvailableModels(),
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'name'           => 'required|string|max:255',
        'emoji'          => 'nullable|string|max:10',
        'persona'        => 'nullable|string|max:2000',
        'context'        => 'nullable|string|max:2000',
        'response_style' => 'nullable|string|max:2000',
        'language'       => 'nullable|string|max:10',
        'model'          => 'required|string',
        'is_default'     => 'boolean',
    ]);

    if ($request->is_default) {
        $request->user()->agents()->update(['is_default' => false]);
    }

    $request->user()->agents()->create($request->all());

    return redirect()->route('agents.index');
}


    public function update(Request $request, Agent $agent)
{
    $this->authorize('update', $agent);

    $request->validate([
        'name'           => 'required|string|max:255',
        'emoji'          => 'nullable|string|max:10',
        'persona'        => 'nullable|string|max:2000',
        'context'        => 'nullable|string|max:2000',
        'response_style' => 'nullable|string|max:2000',
        'language'       => 'nullable|string|max:10',
        'model'          => 'required|string',
        'is_default'     => 'boolean',
    ]);

    if ($request->is_default) {
        $request->user()->agents()->update(['is_default' => false]);
    }

    $agent->update($request->all());

    return redirect()->route('agents.index');
}
    public function destroy(Agent $agent)
{
    $this->authorize('delete', $agent);
    $agent->delete();
    return redirect()->route('agents.index');
}

}
