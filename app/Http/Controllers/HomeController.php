<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    private $post;
    private $user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Post $post, User $user)
    {
        $this->post = $post;
        $this->user = $user;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)     // Add Request since the See all action will be triggered using a form.
    {
        $home_posts         = $this->getHomePosts();
        $suggested_users    = $this->getSuggestedUsers();
        $see_all            = $request->see_all; // Boolean; Based on the value of the button:submit in home.blade.php.
        $show_see_all       = count($suggested_users) > 5;  // Boolean; Show the See all button if there are more than 5 suggested users.

        // If the See all is not clicked, show 5 users only.
        if (!$see_all) {
            $suggested_users = array_slice($suggested_users,0,5);
        }
        
        return view('users.home')
                ->with('home_posts', $home_posts)
                ->with('suggested_users', $suggested_users)
                ->with('see_all', $see_all)
                ->with('show_see_all', $show_see_all);
    }

    # Get the posts of the users that the Auth user is following
    private function getHomePosts()
    {
        $home_posts = [];
        $all_posts = $this->post->latest()->get();

        foreach ($all_posts as $post) {
            if (Auth::user()->id == $post->user_id || $post->user->isFollowed()) {
                $home_posts[] = $post;
            }
        }

        return $home_posts;
    }

    # Get the users that the Auth user is not following
    private function getSuggestedUsers()
    {
        $suggested_users = [];
        $all_users = $this->user->all()->except(Auth::user()->id);

        foreach ($all_users as $user) {
            if (!$user->isFollowed()) {
            // if ($user->id !== Auth::user()->id && !$user->isFollowed())
                $suggested_users[] = $user;
            }
        }

        return $suggested_users;
        /*
        array_slice(x,y,z)
        x -- array
        y -- offset/starting index
        z -- length/how many
        */
    }

    # Search for a user name from the database
    public function search(Request $request)
    {
        $users = $this->user->where('name', 'like', '%'.$request->search.'%')->get();
        
        return view('users.search')
                ->with('users', $users)
                ->with('search', $request->search);
    }
}
