<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(): View
    {
        $clients = Client::withCount('projects')->orderBy('name')->get();

        return view('clients.index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.index')->with('success', "Klien '{$client->name}' berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client): View
    {
        return view('clients.edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', "Data klien '{$client->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified client from storage if no projects attached.
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->projects()->exists()) {
            return back()->with('error', "Klien '{$client->name}' tidak dapat dihapus karena memiliki proyek/kegiatan yang terdaftar.");
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', "Klien berhasil dihapus.");
    }
}
