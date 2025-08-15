<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderMedia extends Model
{
    public $fillable = [
        "user_uuid", "folder_id", "name", "description", "date", "type", "path"
    ];

    public function folder(){
        return $this->belongsTo(Folder::class, "folder_id");
    }
}
