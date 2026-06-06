<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    public function missionVisions()
    {
        return $this->hasMany(MissionVision::class);
    }
    public function pressReleases()
    {
        return $this->hasMany(PressRelease::class);
    }
    public function faqs(){

    return $this->hasMany(Faqs::class);

    }


}
