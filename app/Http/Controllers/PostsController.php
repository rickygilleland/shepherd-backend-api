<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Log;

class PostsController extends Controller
{
    
    public function get_posts(Request $request)
	{
		
		$twenty_four_hours_ago = strtotime("24 hours ago");
		
		$posts = \App\Posts::where('created_at', '<', $twenty_four_hours_ago)->get();
		
		foreach ($posts as &$post) {
			unset($post->lat);
			unset($post->long);
		}	
		
		return ['posts' => $posts];
		
		
	}    
    
    
}