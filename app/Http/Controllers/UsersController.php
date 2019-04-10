<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;

class UsersController extends Controller
{
    
	public function get_user(Request $request)
	{
		
		$user = \App\User::where('id', Auth::id())->first();
		
		if (!$user) {
			return ['success' => false];
		}
		
		//get the number of posts for this user
		$user_stats = (object)[];
		
		$posts = \App\Post::where('user_id', $user->id)->get();
		
		$user_stats->post_count = count($posts);
		
		$user_stats->vote_score = 0;
		
		foreach ($posts as $post) {
			//get the votes for this
			$post_votes = \App\Vote::where('post_id', $post->id)->get();
			
			foreach ($post_votes as $p_vote) {
				if ($p_vote->is_vote_up) {
					$user_stats->vote_score++;
				} else {
					$user_stats->vote_score--;
				}
			}
		}
		
		$votes = \App\Vote::where('user_id', $user->id)->count();
		
		$user_stats->vote_count = $votes;
		
		//calc their total score
		$score = 0;
		
		//10 points for every post made
		$score += $user_stats->post_count * 10;
		//half a point for every vote cast
		$score += $user_stats->vote_count * .5;
		//two points for every vote received (bad or good)
		$score += $user_stats->vote_score * 2;
		
		$user_stats->score = $score;
		
		$comments = \App\Comments::where('user_id', $user->id)->count();
		$user_stats->comments = $comments;
		
		$user_stats->daily_streak = 0;
		
		$returned_user = (object)[];;
		$returned_user->avatar = $user->avatar;
		$returned_user->name = $user->name;
		$returned_user->stats = $user_stats;
		
		return ['user' => $returned_user];
		
	}
	
    
}