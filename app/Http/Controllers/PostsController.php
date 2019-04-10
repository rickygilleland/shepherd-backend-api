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
			  where posts.created_at >= now() - INTERVAL 1 DAY
			  and posts.status = 1
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
				
				$post->voted_up_by_current_user = false;
				$post->voted_down_by_current_user = false;
				
				if ($p_vote->is_vote_up == 1) {
					$total_votes++;
					if ($p_vote->user_id == Auth::id()) {
						$post->voted_up_by_current_user = true;
					}
				} else {
					$total_votes--;
					
					if ($p_vote->user_id == Auth::id()) {
						$post->voted_down_by_current_user = true;
					}
				}
				
			}
			
			$post->votes = $total_votes;
			
			$post->posted_by_current_user = 0;
			
			if ($post->post_user_id == $post->user_id) {
				$post->posted_by_current_user = 1;
			}
			
			//get comments count
			$post->comments = \App\Comment::where('post_id', $post->id)->count();

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
		
		$post->posted_by_current_user = 0;
			
		if ($post->post_user_id == $post->user_id) {
			$post->posted_by_current_user = 1;
		}

		//get all of the votes
		$post_votes = \App\Vote::where('post_id', $post->id)->get();
		
		$total_votes = 0;
		
		foreach ($post_votes as $p_vote) {
				
			$post->voted_up_by_current_user = false;
			$post->voted_down_by_current_user = false;
			
			if ($p_vote->is_vote_up == 1) {
				$total_votes++;
				if ($p_vote->user_id == Auth::id()) {
					$post->voted_up_by_current_user = true;
				}
			} else {
				$total_votes--;
				
				if ($p_vote->user_id == Auth::id()) {
					$post->voted_down_by_current_user = true;
				}
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
			->select('comments.*', 'users.avatar as user_avatar', 'users.id as user_id', 'users.name')
			->orderBy('created_at', 'desc')
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
			//get total post votes
			$votes = \App\Vote::where('post_id', $request->post_id)->get();
			
			$total_votes = 0;
			foreach ($votes as $p_vote) {
				if ($p_vote->is_vote_up) {
					$total_votes++;
				} else {
					$total_votes--;
				}
			}
			
			return ['success' => true, 'total_votes' => $total_votes];
		}
		
		return ['success' => false];
		
	}

	public function report_post(Request $request)
	{
		
		$post = \App\Post::where('id', $request->post_id)->first();
		
		if (!$post) {
			return ['success' => false];
		}
		
		$post_report = new \App\PostReport();
		$post_report->post_id = $request->post_id;
		$post_report->reporter_user_id = Auth::id();
		$post_report->post_author_user_id = $post->user_id;
		$post_report->report_reason = $request->report_reason;
		
		$post_report->save();
		
		//if this post has received more than 5 reports, hide it
		$all_post_reports = \App\PostReport::where('post_id', $post->id)->count();
		
		if ($all_post_reports > 5) {
			$post->status = false;
			$post->save();
		}
		
		return ['success' => true];
		
	}
	
	public function delete_post(Request $request)
	{
		
		$post = \App\Post::where('id', $request->post_id)->first();
		
		if (!$post || $post->user_id != Auth::id()) {
			return ['success' => false];
		}
		
		$post->status = false;
		$post->delete();
		
		return ['success' => true];
		
	}
	
	public function report_comment(Request $request)
	{
		
		$comment = \App\Comment::where('id', $request->comment_id)->first();
		
		if (!$comment) {
			return ['success' => false];
		}
		
		$comment_report = new \App\CommentReport();
		$comment_report->post_id = $request->post_id;
		$comment_report->reporter_user_id = Auth::id();
		$comment_report->post_author_user_id = $comment->user_id;
		$comment_report->report_reason = $request->report_reason;
		
		$comment_report->save();
		
		//if this post has received more than 5 reports, hide it
		$all_comment_reports = \App\CommentReport::where('comment_id', $comment->id)->count();
		
		if ($all_comment_reports > 5) {
			$comment->status = false;
			$comment->save();
		}
		
		return ['success' => true];
		
	}
	
	public function delete_comment(Request $request)
	{
		
		$comment = \App\Post::where('id', $request->comment_id)->first();
		
		if (!$comment || $comment->user_id != Auth::id()) {
			return ['success' => false];
		}
		
		$comment->status = false;
		$comment->delete();
		
		return ['success' => true];
		
	}

    
    
}