<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class  Location extends Model
{
    protected $fillable = [
        "user_id", "name", "description", "latitude", "longitude"
    ];

    public function media(){
        return $this->hasMany(FolderMedia::class);
    }
}
