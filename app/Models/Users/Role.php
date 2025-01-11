<?php

namespace App\Models\Users;

use App\Traits\SerializeDates;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SerializeDates;
    // You might set a public property like guard_name or connection, or override other Eloquent Model methods/properties
}
