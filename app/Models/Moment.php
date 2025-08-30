<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
    protected $fillable = [
        'name', 'location_id', 'description', 'date', 'user_id',
    ];

    public function location(){
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function medias(){
        return $this->hasMany(Media::class, 'moment_id');
    }
}
