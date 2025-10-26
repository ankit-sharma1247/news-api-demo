<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $apiKeys = ApiKey::get();

        return view('api-keys.index', compact('apiKeys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('api-keys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'api_url' => 'required|url',
            'api_key' => 'required|string|max:255',
        ]);

        ApiKey::create($validated);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiKey $apiKey)
    {
        return view('api-keys.show', compact('apiKey'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiKey $apiKey)
    {
        return view('api-keys.edit', compact('apiKey'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'api_url' => 'required|url',
            'api_key' => 'required|string|max:255',
        ]);

        $apiKey->update($validated);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()->route('api-keys.index')
            ->with('success', 'API key deleted successfully.');
    }
}
