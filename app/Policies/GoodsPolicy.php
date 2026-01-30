<?php

namespace App\Policies;

use App\Models\FilamentAdmin;

class GoodsPolicy
{
    public function viewAny(FilamentAdmin $user): bool
    {
        return true;
    }

    public function view(FilamentAdmin $user, $record): bool
    {
        return true;
    }

    public function create(FilamentAdmin $user): bool
    {
        return true;
    }

    public function update(FilamentAdmin $user, $record): bool
    {
        return true;
    }

    public function delete(FilamentAdmin $user, $record): bool
    {
        return true;
    }

    public function restore(FilamentAdmin $user, $record): bool
    {
        return true;
    }

    public function forceDelete(FilamentAdmin $user, $record): bool
    {
        return true;
    }
}
