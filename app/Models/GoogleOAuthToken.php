<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleOAuthToken extends Model
{
    protected $table = 'google_oauth_tokens';

    protected $fillable = [
        'email',
        'refresh_token',
        'scopes',
        'authorized_at',
        'history_id',
        'last_synced_at',
        'last_sync_count',
        'last_sync_error',
    ];

    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
            'scopes' => 'array',
            'authorized_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_sync_count' => 'integer',
        ];
    }
}
