<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Connection;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // Se o novo usuário não for admin/superadmin (ou seja, é membro comum)
        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            
            // Buscar todos os admins (Excluir superadmin)
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                // Criar conexão aceita
                Connection::firstOrCreate([
                    'requester_id' => $user->id,
                    'requested_id' => $admin->id
                ], [
                    'status' => 'accepted'
                ]);
            }
        }
    }
}
