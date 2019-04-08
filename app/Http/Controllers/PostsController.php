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
		

		$posts = DB::select("
			  select posts.id, (
			    6371 * acos (
			      cos ( radians(".$request->location_lat.") )
			      * cos( radians( location_lat ) )
			      * cos( radians( location_long ) - radians(".$request->location_long.") )
			      + sin ( radians(".$request->location_lat.") )
			      * sin( radians( location_lat ) )
			    )
			  ) AS distance,
			  content,
			  avatar as user_avatar,
			  users.name as user_name
			  from posts
			  join users on users.id = posts.user_id
			  HAVING distance < 2
			");
			
		foreach ($posts as &$post) {
			
			$name = explode(' ', trim($post->user_name));
			$post->user_name = $name[0];
			
			//make the posted date stylized
			$hour_ago = strtotime("-60 minutes");
			
			if ($post->created_at > $hour_ago) {
				//less than an hour ago, show the minutes since posting
				$minutes_since_posting = (time() - $post->created_at) / 60000;
				
				$post->display_posted_time = $minutes_since_posting . " mins";
			} else {
				//more than an hour, show the number of hours since posting
				$minutes_since_posting = (time() - $post->created_at) / 60000;
				
				$hours_since_posting = round(($minutes_since_posting / 60), 0, PHP_ROUND_HALF_UP);
				
				$post->display_posted_time = $hours_since_posting . "h";
				
			}

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