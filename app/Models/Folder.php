<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    public $fillable = [
        "user_uuid", "name", "description", "total_files", "cover"
    ];
}
