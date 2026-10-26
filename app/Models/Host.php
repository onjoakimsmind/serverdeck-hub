<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Host extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'token_hash', 'last_seen_at', 'last_status', 'payload'];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function samples(): HasMany
    {
        return $this->hasMany(HostSample::class);
    }

    /**
     * A host is considered online if it reported within twice its expected
     * interval. We don't know the host's configured interval here, so this
     * uses a fixed, generous window rather than guessing — being too eager
     * to mark a host "offline" is worse than being a little slow about it.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes(15));
    }
}
