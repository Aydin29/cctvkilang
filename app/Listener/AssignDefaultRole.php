<?php

namespace App\Listeners;

use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignDefaultRole
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Logika untuk memberikan role 'user' sebagai default
        $assignRoleLogic = function () use ($event) {
            $userRole = Role::where('slug', 'user')->first();
            if ($userRole) {
                $event->user->roles()->attach($userRole);
            } else {
                // Log jika role 'user' tidak ditemukan, untuk debugging
                Log::warning("Default role 'user' not found for user: {$event->user->email}");
            }
        };

        // SOLUSI: Periksa apakah sudah ada transaksi yang aktif
        if (DB::transactionLevel() > 0) {
            // Jika ya (misalnya dari test), langsung jalankan logika
            $assignRoleLogic();
        } else {
            // Jika tidak, bungkus dengan transaksi baru
            DB::transaction($assignRoleLogic);
        }
    }
}
