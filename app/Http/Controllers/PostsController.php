<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post;
use DB;

class PostsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$post = Post::where('title','Post Two')->get();
        /* For manual DB queries, set 'use DB;' at the top */
        //$posts = DB::select('SELECT * FROM posts ORDER BY created_at DESC');

        /* Query to take limited posts */
        //$posts = POST::orderBy('created_at', 'desc')->take(1)->get();
        
        //This query is for taking all posts in descending order
        //$posts = Post::orderBy('created_at','desc')->get();

        //pagination of 2 posts per page
        $posts = Post::orderBy('created_at','desc')->paginate(2);
        return view('posts.index')->with('posts', $posts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        //Handle file upload
        if ($request->hasFile('cover_image')) {
            //Get filename with extension (e.g. 'imageOne.jpg')
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            //Get filename only (e.g. 'imageOne')
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //Get extension only (e.g. 'jpg')
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            //Filename to store    
            $filenameToStore = $filename.'_'.time().'.'.$extension;
            //Upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $filenameToStore);
        
        } else {
            $filenameToStore = 'noimage.jpg';
        }

        //Create New Post
        $post = new Post;
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id;
        $post->cover_image = $filenameToStore;
        $post->save();
        
        return redirect('/posts')->with('success', 'Post Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);

        //Check for correct user
        if (auth()->user()->id != $post->user_id) {
            return redirect('/posts')->with('error', 'Unauthorized Page');
        }

        return view('posts.edit')->with('post', $post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'
        ]);

        //Handle file upload
        if ($request->hasFile('cover_image')) {
            //Get filename with extension (e.g. 'imageOne.jpg')
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            //Get filename only (e.g. 'imageOne')
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //Get extension only (e.g. 'jpg')
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            //Filename to store    
            $filenameToStore = $filename.'_'.time().'.'.$extension;
            //Upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $filenameToStore);
        }

        //Edit Post
        $post = Post::find($id);
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        if ($request->hasFile('cover_image')) {
            //Check if previous image existed
            if ($post->cover_image != 'noimage.jpg') {
                //Delete previous image linked to this post
                Storage::delete('public/cover_images/'.$post->cover_image);
            }
            //Insert new image name
            $post->cover_image = $filenameToStore;
        }
        $post->save();
        
        return redirect('/posts')->with('success', 'Post Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        //Check for correct user
        if (auth()->user()->id != $post->user_id) {
            return redirect('/posts')->with('error', 'Cannot delete an unauthorized Page');
        }

        if ($post->cover_image != 'noimage.jpg') {
            //Delete image
            Storage::delete('public/cover_images/'.$post->cover_image);
        }

        $post->delete();

        return redirect('/posts')->with('success', 'Post Removed');
    }
}
