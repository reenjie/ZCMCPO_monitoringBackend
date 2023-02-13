<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Accesstoken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        //dataid, name, username, email, role, created
        echo 'aw';
    }


    public function signIn(Request $request)
    {
        $credentials = array(
            'username' => $request->username,
            'password' => $request->password
        );

        if (Auth::attempt($credentials)) {
            $accesstoken = hash('sha256', $plainTextToken = Str::random(40));
            $expires = date('Y-m-d H:i:s', strtotime('+60 minutes'));

            $validate = Accesstoken::where('userID', Auth::user()->id);
            if (count($validate->get()) >= 1) {
                $validate->update([
                    'token' => $accesstoken,
                    'expires_at' => $expires,
                ]);
            } else {
                $validate->create([
                    'roleID'     => Auth::user()->roleID,
                    'userID'     => Auth::user()->id,
                    'token'      => $accesstoken,
                    'username'   => $request->username,
                    'expires_at' => $expires,
                ]);
            }


            return response()->json(
                [
                    'message' => 'Login Success',
                    'token'   =>  $accesstoken,
                    'role'    => Auth::user()->roleID,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'Invalid Credentials',
                ],
                401
            );
        }
    }

    public function fetchuser(Request $request)
    {
        $token = $request->token;
        $datetime = date('Y-m-d H:i:s');
        //check first if user is authenticated
        $user = DB::select('SELECT u.*, r.roles FROM `users` u INNER JOIN roles r WHERE u.id in 
        ( select userID from accesstokens where token ="' . $token . '" )');

        if (count($user) >= 1) {
            $userID = $user[0]->id;
            $validate = Accesstoken::where('userID', $userID);
            foreach ($validate->get() as $access) {
                $expiry = $access->expires_at;
                if ($datetime > $expiry) {
                    return response()->json(
                        [
                            'message' => 'Unauthenticated',
                            'status'  => '401'
                        ],
                        401
                    );
                } else {
                    return response()->json(
                        [
                            'data' => $user,
                        ],
                        200
                    );
                }
            }
        } else {
            return response()->json(
                [
                    'message' => 'Unauthenticated',
                ],
                401
            );
        }
    }

    public function store(Request $request)
    {

        $save =  User::create([
            'name' => $request->name,
            'username' => $request->username,
            'roleID' => $request->srole,
            'email' => $request->email,
            'password' => Hash::make($request->pass),
        ]);

        if ($save) {
            return response()->json(
                [
                    'message' => 'success',
                ],
                200
            );
        }
    }
}
