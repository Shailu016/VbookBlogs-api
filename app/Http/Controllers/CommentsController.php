<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Comments $comments, Post $post)
    {
        $comments = Comments::where('post_id', $post->id)->get();
        return response()->json($comments);
    }
   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Comments $comments, Post $post)
    {
        request()->validate([
            'body' => 'required'
            
        ]);
       
        
    $comments = Comments::create([
        //'user_id' => 1,
     'user_id' => Auth::id(),
     "post_id" => $post->id,
    'body' => request('body')
   ]);
   
   return response()->json($comments);
 
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comments $comments, Post $post)
    {
        $comments = Comments::where("post_id", $post->id)->first();
      
        return response()->json($comments);
    }

    public function delete(Comments $comments)
    { 
        $comments->delete();
        return response()->json($comments);
    }

    
   
}
