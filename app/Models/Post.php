<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Bookmark;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'excerpt',
        'body',
        'tags',
        'image_path',
        'status',
    ];
    

    

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function likes()
    {
        return $this->hasMany(Likes::class, 'post_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id' );
    }
    public function bookmarks()
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'user_id', 'post_id');
    }

    public function is_bookmarked(User $user)
    {
        return $this->bookmark->contains($user);
    }
    
    public function getImagePathAttribute($value)
    {
        return asset('images/' . $value);
    }

    public function getTagsAttribute($value)
    {
        return json_decode($value);
    }
    
    
}
