<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "sub_title",
        "description",
        "image",
        "body",
        "position",
        "url",
        "status",
        "created_by",
        "deleted_at"
    ];
}
