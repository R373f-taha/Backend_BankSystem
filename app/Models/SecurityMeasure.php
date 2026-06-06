<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityMeasure extends Model
{

    protected $guarded = [];

  public function userSecurities()
    {
        return $this->hasMany(UserSecurity::class);
    }
}
