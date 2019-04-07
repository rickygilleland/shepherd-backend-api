<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;

class PostsController extends Controller
{
    
    public function get_posts(Request $request)
	{
		
		//create a dummy post every time this request is made for testing
		$post = new \App\Post();	
		$post->content = Str::random(60);
		$post->user_id = Auth::id();
		$post->location_lat	= "89";
		$post->location_long = "90";
		
		$post->save();
		
		
		$twenty_four_hours_ago = strtotime("24 hours ago");
		
		$posts = \App\Post::where('created_at', '<', $twenty_four_hours_ago)->get();
		
		foreach ($posts as &$post) {
			unset($post->location_lat);
			unset($post->location_long);
		}	
		
		return ['posts' => $posts];
		
		
	}    
    
    
}