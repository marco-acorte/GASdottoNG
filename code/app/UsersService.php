<?php

namespace App;

use Auth;
use App\User;
use App\Exceptions\AuthException;

class UsersService
{
	
	public function list()
	{
		if (!Auth::check()) {
			throw new AuthException(401);
		}
		
		$user = Auth::user();
		if ($user->gas->userCan('users.admin|users.view') == false)
		{
			throw new AuthException(403);
		}

		$users = User::orderBy('lastname', 'asc')->get();
		
		return $users;
	}
	
}