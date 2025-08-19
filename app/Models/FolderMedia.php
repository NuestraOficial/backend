<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderMedia extends Model
{
    public $fillable = [
        "user_uuid", "folder_id", "location_id", "name", "description", "date", "type", "path"
    ];

    public function folder(){
        return $this->belongsTo(Folder::class, "folder_id");
    }

    public function location(){
        return $this->belongsTo(Location::class, "location_id");
    }
}
