<?php

namespace App\Http\Controllers;

use App\Models\Host;
use App\Services\HostEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HostController extends Controller
{
    public function index(): Response
    {
        $hosts = Host::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Host $host) => [
                'id' => $host->id,
                'name' => $host->name,
                'online' => $host->isOnline(),
                'last_seen_at' => $host->last_seen_at?->toIso8601String(),
                'last_status' => $host->last_status,
                'payload' => $host->payload,
                'created_at' => $host->created_at->toIso8601String(),
            ]);

        return Inertia::render('Hosts/Index', [
            'hosts' => $hosts,
        ]);
    }

    public function store(Request $request, HostEnrollmentService $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $generated = $enrollment->generateToken();

        Host::create([
            'name' => $validated['name'],
            'token_hash' => $generated['hash'],
        ]);

        // The raw token is shown exactly once, via a one-time flash value.
        // It is never persisted and cannot be retrieved again — if it's
        // lost, the fix is to remove the host and enroll a new one.
        return back()->with('new_token', $generated['token']);
    }

    public function destroy(Host $host): RedirectResponse
    {
        $host->delete();

        return back()->with('success', "Removed {$host->name} from the Hub.");
    }
}
