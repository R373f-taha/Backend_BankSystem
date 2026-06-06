<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $guarded = [];

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function benefits()
    {
        return $this->belongsToMany(Benefit::class);
    }
}
