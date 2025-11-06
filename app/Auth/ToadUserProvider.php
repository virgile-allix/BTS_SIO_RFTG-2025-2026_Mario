<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ToadUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $data = session('toad_user');
        $id = $data['id'] ?? $data['email'] ?? null;

        if ($data && $id == $identifier) {
            return new ToadUser($data);
        }
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null; // pas de remember token
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // no-op
    }

    public function retrieveByCredentials(array $credentials)
    {
        return null; // on ne valide pas ici (fait via l’API Toad)
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false; // non utilisé
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // non utilisé (pas de password local)
    }
}