<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use App\Models\Post;
use App\Models\Likes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller{
   
    public function store(Request $request)
    {
            $user = User::where('id', Auth::id())->first();     
            $user->name = request('name');
            $user->youtube = request('youtube');
            $user->facebook = request('facebook');
            $user->instagram = request('instagram');
            $user->bio = request('bio');
           
            

                if (request()->hasFile('image')) {
                    $imagePath = time() . $request->name . '.' . $request->image->extension();
                    $request->image->move(public_path('images'), $imagePath);
                    $oldImagePath = public_path('images') . "\\" . $user->image_path;

                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }

                    $user->image_path = $imagePath;
                }
                $user->save();
                
               return  response()->json([
                   "name" => $user->name,
                   "image_path" => $user->image_path,
                   "youtube" => $user->youtube,
                   "facebook" => $user->facebook,
                   "instagram" => $user->instagram,
                   "bio" => $user->bio,
               ]);
            
           
}

  
    public function all_users(Profile $profile)
    {
       $users =  User::sum('subscribe');
       $post  =  Post::count('user_id');
       $like  =  Likes::count('like');
      
       return response()->json([

         'Subscriber' => $users,
         'PostCount' => $post,
         'LikeCount' => $like, ]);
      
        }


}
