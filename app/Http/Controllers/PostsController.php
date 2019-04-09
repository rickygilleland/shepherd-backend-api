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
		
		
		if (isset($request->sort)) {
			if ($request->sort == "hot") {
				
			}
		}
		

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
			  posts.created_at,
			  avatar as user_avatar,
			  users.name as user_name,
			  users.id as user_id,
			  posts.user_id as post_user_id
			  from posts
			  join users on users.id = posts.user_id
			  HAVING distance < 5
			");
			
		foreach ($posts as &$post) {
			
			$name = explode(' ', trim($post->user_name));
			$post->user_name = $name[0];
			
			//make the posted date stylized
			$hour_ago = strtotime("-60 minutes");
			
			$created_at = strtotime($post->created_at);
			
			$post->created_at_epoch = $created_at;
			
			$minutes_since_posting = (time() - $created_at) / 60;
			
			if ($created_at > $hour_ago) {
				//less than an hour ago, show the minutes since posting
				$minutes_since_posting = round($minutes_since_posting, 0, PHP_ROUND_HALF_UP);
				
				$post->display_posted_time = $minutes_since_posting . " mins";
			} else {
				//more than an hour, show the number of hours since posting				
				$hours_since_posting = round(($minutes_since_posting / 60), 0, PHP_ROUND_HALF_UP);
				
				$post->display_posted_time = $hours_since_posting . "h";
				
			}
			
			//get all of the votes
			$post_votes = \App\Vote::where('post_id', $post->id)->get();
			
			$total_votes = 0;
			
			foreach ($post_votes as $p_vote) {
				if ($p_vote->is_vote_up == 1) {
					$total_votes++;
				} else {
					$total_votes--;
				}
			}
			
			$post->votes = $total_votes;
			
			$post->posted_by_current_user = false;
			
			if ($post->post_user_id == $post->user_id) {
				$post->posted_by_current_user = true;
			}

		}
		
		if (isset($request->sort) && $request->sort == "recent") {
			usort($posts, function ($a, $b) {return $a->created_at_epoch < $b->created_at_epoch;});
		} else {
			usort($posts, function ($a, $b) {return $a->votes < $b->votes;});
		}

		return ['posts' => $posts];
		
		
	}    
	
	public function get_post(Request $request)
	{
		
		if (!isset($request->post_id) || $request->post_id == '') {
			return ['success' => false];
		}		
		
		//get the post
		$post = \App\Post::where('id', $request->post_id)->first();
		
		if (!$post) {
			return ['success' => false];
		}
		
		//get the user that posted the post
		$post_user = \App\User::where('id', $post->user_id)->first();
		
		
		$post->user_avatar = $post_user->avatar;
		$post->user_name = $post_user->name;
		$post->user_id = $post_user->id;
		
		$name = explode(' ', trim($post->user_name));
		$post->user_name = $name[0];


		//make the posted date stylized
		$hour_ago = strtotime("-60 minutes");
		
		$created_at = strtotime($post->created_at);
		
		$post->created_at_epoch = $created_at;
		
		$minutes_since_posting = (time() - $created_at) / 60;
		
		if ($created_at > $hour_ago) {
			//less than an hour ago, show the minutes since posting
			$minutes_since_posting = round($minutes_since_posting, 0, PHP_ROUND_HALF_UP);
			
			$post->display_posted_time = $minutes_since_posting . " mins";
		} else {
			//more than an hour, show the number of hours since posting				
			$hours_since_posting = round(($minutes_since_posting / 60), 0, PHP_ROUND_HALF_UP);
			
			$post->display_posted_time = $hours_since_posting . "h";
			
		}

		//get all of the votes
		$post_votes = \App\Vote::where('post_id', $post->id)->get();
		
		$total_votes = 0;
		
		foreach ($post_votes as $p_vote) {
			if ($p_vote->is_vote_up == 1) {
				$total_votes++;
			} else {
				$total_votes--;
			}
		}
		
		$post->votes = $total_votes;
		
		
		return ['post' => $post];
		
		
	}   
	
	public function get_post_comments(Request $request)
	{
		
		//get all of the comments for this post
		$comments = \App\Comment::where('post_id', $request->post_id)
			->join('users', 'users.id', '=', 'comments.user_id')
			->select('comments.*', 'users.avatar_url as user_avatar', 'users.id as user_id', 'users.name')
			->get();
			
		if (!$comments) {
			return ['success' => false];
		}
			
		foreach ($comments as &$comment) {
			$name = explode(' ', trim($comment->name));
			$comment->user_name = $name[0];
			
			//make the posted date stylized
			$hour_ago = strtotime("-60 minutes");
			
			$created_at = strtotime($comment->created_at);
			
			$comment->created_at_epoch = $created_at;
			
			$minutes_since_posting = (time() - $created_at) / 60;
			
			if ($created_at > $hour_ago) {
				//less than an hour ago, show the minutes since posting
				$minutes_since_posting = round($minutes_since_posting, 0, PHP_ROUND_HALF_UP);
				
				$comment->display_posted_time = $minutes_since_posting . " mins";
			} else {
				//more than an hour, show the number of hours since posting				
				$hours_since_posting = round(($minutes_since_posting / 60), 0, PHP_ROUND_HALF_UP);
				
				$comment->display_posted_time = $hours_since_posting . "h";
				
			}
		}

		return ['comments' => $comments];
		
	} 
	
	public function add_comment(Request $request)
	{
		
		if ($request->content == '' || $request->content == null) {
			return ['success' => false];
		}
		
		$comment = new \App\Comment();
		$comment->content = $request->content;
		$comment->user_id = Auth::id();
		$comment->post_id = $request->post_id;
		$comment->status = 1;
		
		
		if ($comment->save()) {
			return ['success' => true];
		}
		
		return ['success' => false];
		
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
	
	public function vote(Request $request)
	{

		//check if they have already voted for this post
		$vote = \App\Vote::where('user_id', Auth::id())->where('post_id', $request->post_id)->first();		
		
		if (!$vote) {
		
			$vote = new \App\Vote();
			
		}
		
		if ($request->vote_type == "vote_up") {
			$vote->is_vote_up = 1;
			$vote->is_vote_down = 0;
		} else {
			$vote->is_vote_up = 0;
			$vote->is_vote_down = 1;
		}
		
		$vote->post_id = $request->post_id;
		$vote->user_id = Auth::id();
		
		if ($vote->save()) {
			return ['success' => true];
		}
		
		return ['success' => false];
		
	}

    
    
}