<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Location extends Model
{
    protected $fillable = [
        "user_id", "name", "description", "latitude", "longitude"
    ];

    public function medias(){
        return $this->hasMany(Media::class);
    }
}
