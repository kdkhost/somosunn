<?php

namespace App\Models;

use App\Models\Concerns\ChecksTableAvailability;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot
{
    use ChecksTableAvailability;

    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_STAFF = 'staff';

    protected $table = 'company_users';

    protected $fillable = [
        'company_id',
        'user_id',
        'role',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
