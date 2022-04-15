<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;
    protected $fillable = [
        'body',
        'user_id',
        'post_id',
        'user_name',
        'image_path'
    ];
    //append user name
    protected $appends = [
        'user_name',
        
    ];
    //append user name
    public function getUserNameAttribute()
    {
        return $this->belongsTo(User::class, 'user_id')->first()->name;
    }
    
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }
    public $table = "comments";

    public function posts()
    {
        return $this->belongsTo(Post::class, );
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', );
    }
}
