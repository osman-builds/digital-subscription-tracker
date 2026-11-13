<?php

namespace App\Auth;

use App\Services\SandboxService;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Auth provider that resolves an in-memory virtual user for sandbox sessions
 * without storing or querying demo credentials in the database.
 */
class SandboxUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        if ((int) $identifier === SandboxService::SANDBOX_USER_ID && session()->has(SandboxService::KEY_EXPIRES_AT)) {
            return SandboxService::makeVirtualUser();
        }

        return parent::retrieveById($identifier);
    }

    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable
    {
        if ((int) $identifier === SandboxService::SANDBOX_USER_ID) {
            return null;
        }

        return parent::retrieveByToken($identifier, $token);
    }
}
