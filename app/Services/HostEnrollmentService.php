<?php

namespace App\Services;

use App\Models\Host;
use Illuminate\Support\Str;

/**
 * Enrollment tokens are bearer secrets used only to authenticate an
 * ingest POST as coming from a specific host (docs/design/hub.md §4.2). The
 * raw token is shown to the admin exactly once, at creation time; only its
 * SHA-256 hash is ever stored, so it can be looked up but never displayed
 * again.
 */
class HostEnrollmentService
{
    /**
     * @return array{token: string, hash: string}
     */
    public function generateToken(): array
    {
        $token = Str::random(40);

        return [
            'token' => $token,
            'hash' => $this->hash($token),
        ];
    }

    public function findByToken(string $token): ?Host
    {
        if ($token === '') {
            return null;
        }

        return Host::query()->where('token_hash', $this->hash($token))->first();
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
