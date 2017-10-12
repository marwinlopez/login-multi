<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\SocialProvider;
use Socialite;

class SocialAuthController extends Controller
{
    public function facebook()
    {
    	return Socialite::driver('facebook')->redirect();
    }

    public function callback()
    {
    	$user = Socialite::driver('facebook')->user();

    	$existing = User::whereHas('socialProvider',function($query) use ($user){
    		$query->where('provider_id',$user->id);
    	})->first();

    	if($existing !== null){
    		auth()->login($existing);

    		return redirect('/home');
    	}
    	
    	session()->flash('facebookUser',$user);

    	return view('users.facebook',compact('user'));
    }

    public function register(Request $request)
    {
    	$data = session('facebookUser');

    	$username = $request->input('username');

    	$user = User::create([
    		'name'		=>	$data->name,
    		'email'		=>	$data->email,
    		'avatar'	=>	$data->avatar,
    		'username'	=>  $username,
    		'password'	=>	str_random(16),
    		// 'remember_token' => str_random(10),	
    	]);

    	// dd($user);

    	$provider = SocialProvider::create([
    		'user_id'		=> $user->id,
			'provider_id'	=> $data->id,	
			'provider'		=> 'facebook',
    	]);

    	auth()->login($user);

    	return redirect('/home');
    }
}
