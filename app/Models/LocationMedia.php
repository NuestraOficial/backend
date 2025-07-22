<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationMedia extends Model
{
    protected $fillable = ['location_id', 'type', 'path'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    protected static function boot(){
        parent::boot();

        static::deleting(function ($image) {
            $path = public_path($image->path);

            if (file_exists($path)) {
                unlink($path);
            }
        });
    }

}
