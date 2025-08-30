<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public $table = "medias";
    
    public $fillable = [
        "user_id", "folder_id", "location_id", "moment_id", "name", "description", "date", "type", "path"
    ];

    public function folder(){
        return $this->belongsTo(Folder::class, "folder_id");
    }

    public function location(){
        return $this->belongsTo(Location::class, "location_id");
    }
    
    public function moment(){
        return $this->belongsTo(Moment::class, "moment_id");
    }
}
