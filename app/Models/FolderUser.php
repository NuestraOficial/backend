<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderUser extends Model
{
    public $table = "folders_user";

    public $fillable = [
        "folder_id", "user_uuid"
    ];
}
