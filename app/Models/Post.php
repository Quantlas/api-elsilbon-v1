<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'sub_title',
        'description',
        'short_description',
        'slug',
        'main_image',
        'category_id',
        'body',
        'status',
        'views',
        'created_by',
        'updated_by',
        'deleted_at'
    ];
}
