<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;

class PostsController extends Controller
{
    
    public function get_posts(Request $request)
	{

		$twenty_four_hours_ago = strtotime("24 hours ago");
		

		$posts = DB::table('posts')->selectRaw("
			  id, (
			    6371 * acos (
			      cos ( radians(".$request->location_lat.") )
			      * cos( radians( location_lat ) )
			      * cos( radians( location_long ) - radians(".$request->location_long.") )
			      + sin ( radians(".$request->location_lat.") )
			      * sin( radians( location_lat ) )
			    )
			  ) AS distance,
			  content,
			")->havingRaw("HAVING distance < 2")->get();

		
		foreach ($posts as &$post) {
			unset($post->location_lat);
			unset($post->location_long);
		}	
		
		return ['posts' => $posts];
		
		
	}    
	
	public function add_post(Request $request)
	{
		
		if ($request->content == '' || $request->content == null) {
			return ['success' => false];
		}
		
		$post = new \App\Post();
		$post->content = $request->content;
		$post->user_id = Auth::id();
		$post->location_lat = $request->location_lat;
		$post->location_long = $request->location_long;
		
		
		if ($post->save()) {
			return ['success' => true];
		}
		
		return ['success' => false];
		
	}
    
    
}