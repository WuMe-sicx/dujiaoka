<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class FilamentAdmin extends Authenticatable implements FilamentUser
{
    protected $table = 'admin_users';

    protected $fillable = [
        'username',
        'password',
        'name',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Determine if the user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the user's display name for Filament.
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->username ?? 'Admin';
    }

    /**
     * Get the name of the unique identifier for the user.
     * Use username instead of email for authentication.
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }
}
