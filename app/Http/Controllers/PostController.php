<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Profile;
use App\Models\Likes;
use App\Models\Category;
use App\Models\User;
use App\Models\Views;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Events\PostPublished;
use App\Notifications\PostCreated;
use Carbon\Carbon; 
use Illuminate\Support\Arr;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
      
        $user = User::where('slug', $user->slug)->first();
       
        if(!$user) {
            
           return "User not found";

        }else{

            $post = Post::withcount('likes', 'comments')->where('user_id', $user->id )->get();
            
         return response()->json($post);
        }
    } 


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required',
                'excerpt' => 'required',
                'body' => 'required',
                'image' => 'mimes:jpg,png,jpeg,webp|max:50480'
                

            ]
        );

        try {
            

            
            if(isset($request->image)) {

                $imagePath = time() . $request->name . '.'. $request->image->extension();
                $request->image->move(public_path('images'), $imagePath);
                
                $user = User::where('id', Auth::id())->first();
           
            }
           
            $posts = new Post();
            $posts->name = request('name');
            $posts->excerpt = request('excerpt');
            $posts->body = request('body');
            $posts->tags = strtolower(  json_encode(request('tags')));
            
            $posts->image_path = $imagePath ?? null;
           
            $posts->user_id = Auth::id();
            $posts->category_id = request('category_id');
          
            $posts->save();
            
            $user = User::where('id', Auth::id())->where('subscribe', 1)->first();
            if($user){

                $user->notify(new PostCreated('New Post Created'));
            }
            return response()->json($posts); 
          
        } catch (\Exception $e) {

            return $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, Post $post)
    {
     
     $views = Views::where('post_id', $post->id)->whereDate('created_at',  Carbon::today()->toDateString())->first();

     if(!$views){

        $views = Views:: updateOrCreate([
            'post_id' => $post->id,
            'views' => 1
        ]);

    }else{
        
        $views->views = $views->views + 1;
        $views->save();
    }

        $user = User::where('slug', $user->slug)->first();
        $post = Post::where('id', $post->id)->where('user_id', $user->id)->first();
         return response()->json($post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function user_all_post(Post $post, User $user)
    {
        $post = Post::where('user_id', $user->id)->get();
        return $post;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    { 
            $request->validate(
                [
                    'name' => 'required',
                    'excerpt' => 'required',
                    'body' => 'required',
                    'image' => 'mimes:jpg,png,jpeg,webp|max:50480'

                ]
            );
            try {

                $post->name = request('name');
                $post->excerpt = request('excerpt');
                $post->body = request('body');
                $post->category_id = request('category_id');


                if (request()->hasFile('image')) {
                    $imagePath = time() . $request->name . '.' . $request->image->extension();
                    $request->image->move(public_path('images'), $imagePath);
                    $oldImagePath = public_path('images') . "\\" . $post->image_path;

                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }

                    $post->image_path = $imagePath;
                }
                $post->save();

                return response()->json($post);

            } catch (\Exception $e) {
                
                return $e->getMessage();
            }
        }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */

    public function delete(Post $post)
    {
        
        $post->delete();
        return "Post deleted successfully";

    }


    public function restore(int $id)
    {

        $blog = Post::onlyTrashed()->findOrFail($id);
        
        if (!$blog) {

               return response()->json(['message' => 'Post not found'], 404);
       
            }else{
            
                $blog->restore();
                return response()->json($blog, 200);
          
        }

    }

    public function usersPost()
    {
        $post = Post::where('user_id', Auth::id())->get();
        return $post ;
    }

   
    public function category(Category $category)
    {
         $post = Post::where('category_id', $category->id)->get();
         return $post;
    }

   
    public function statusUpdateDraft(Post $post)
    {
      
        $post=  $post->update(['status' => "Draft"]);
         return response()->json([
            'message' => 'Post status updated successfully',
            'post' => "Draft"
        
        ]);
       
    }

    
    public function statusUpdateArchive(Post $post)
    {
        if($post->status == "published"){

            return response()->json([
                'message' => 'Post status updated successfully',
                'post' => "Archived"
            
            ]);
        }
        else{
            $post = $post->update(['status' => "published"]);
            return response()->json([
                'message' => 'Post status not updated',
                'post' => "Published"
            
            ]);
        }
        
       
    }

    
    public function post_by_tags(Request $request)
    {
        
        $post = Post::where('tags', 'like', '%' . $request->tags . '%')->get();
        return $post;

    }

    public function post_views(Post $post)
    {
      
        $todaysviews =  Views::whereDate('created_at',  Carbon::today()->toDateString())->where('post_id', $post->id)->count();
         
        $date = Carbon::now()->subDays(7);
        $weeklyViews = Views::whereDate('created_at', '>=', $date)->where('post_id', $post->id)->count();
       
       $date = Carbon::now()->subDays(30);
       $mothlyViews = Views::whereDate('created_at', '>=', $date)->where('post_id', $post->id)->count();

       $totalViews = Views::where('post_id', $post->id)->count();
// noob application bana hai pls improve itna bura code kabi dekha nahi hai 

       
       $date = Carbon::now()->subDays(5);
         $Last_five_days = Views::whereDate('created_at', '>=', $date)->where('post_id', $post->id)->get();

       return response()->json([
           'todays_Views' => $todaysviews,
           'weekly_Views' => $weeklyViews,
           'mothly_Views' => $mothlyViews,
           'total_Views' => $totalViews,
            'Last_five_days' => $Last_five_days
       ]);

    
    }
    
   public function all_tags()
   { 
    
    $tags = Post::select('tags')->get();

    $result = $tags->map(function ($tag, $key) {

        return json_decode($tag->tags);

    });
    
    $data = array_values(array_unique(Arr::flatten($result)));
    
    return response([

        'data' => $data
    ]);

   }


   
}
