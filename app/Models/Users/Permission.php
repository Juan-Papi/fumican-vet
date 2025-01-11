<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function menu(): HasOne
    {
        return $this->hasOne(Menu::class);
    }
}
