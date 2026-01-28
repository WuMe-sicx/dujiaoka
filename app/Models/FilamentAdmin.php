<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class FilamentAdmin extends Administrator implements FilamentUser, Authenticatable
{
    protected $table = 'admin_users';

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

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Get the password hash attribute for the user.
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }
}
