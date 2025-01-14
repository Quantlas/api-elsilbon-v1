<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "sub_title",
        "description",
        "image",
        "body",
        "position",
        "status",
        "created_by",
        "deleted_at"
    ];
}
