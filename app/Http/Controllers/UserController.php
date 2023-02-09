<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    public function signIn(Request $request)
    {
        $credentials = array(
            'username' => $request->username,
            'password' => $request->password
        );

        if (Auth::attempt($credentials)) {
            echo 'success';
        } else {
            echo 'failed';
        }
    }
}
