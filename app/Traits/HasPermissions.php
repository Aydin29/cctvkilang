<?php

namespace App\Traits;

trait HasPermissions
{
    /**
     * Check if the user has permission to perform an action
     */
    protected function userCan(string $permission): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Admin bypass all permission checks
        return $user->isAdmin();
    }

    /**
     * Abort if user doesn't have permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->userCan($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
