<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationUser extends Model
{
    protected $fillable = [
        "location_id", "user_id"
    ];
}
