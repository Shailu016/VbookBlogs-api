<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
class SearchController extends Controller
{
    public function search(Request $request, User $user)
    {
        $user = User::where('slug', $user->slug)->first();
        //if no user found then
        if(!$user->site) {
            
           return "User not found";
        }
        
        else{
            // get user input and search for it
            $search = $request->input('query');
            //search all post of user according to user input match user input with name and body of post
            $post = Post::where('user_id', $user->id )->where('name', 'like', '%' . $search . '%')->orWhere('body', 'like', '%' . $search . '%')->get();
            //return the result
            return response()->json($post);
        }
    }
    

     
}
