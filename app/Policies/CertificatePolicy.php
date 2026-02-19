<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Certificate $certificate)
    {
        // Allow if user is the owner of the certificate
        if ($user->id === $certificate->user_id) {
            return true;
        }

        // Allow if user is admin (optional, depending on requirements)
        // Assumption: Admin can view any certificate. 
        // We can check a permission or role here.
        if ($user->isAdmin() || $user->hasRole('admin') || $user->hasPermissionTo('certificates_access')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Certificate $certificate)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Certificate $certificate)
    {
        return $user->isAdmin();
    }
}
