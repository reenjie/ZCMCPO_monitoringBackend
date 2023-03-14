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
        try {
            $data = DB::select('SELECT u.id as dataid, u.name , u.username , u.email , r.roles,  DATE_FORMAT(u.created_at, "%d/%m/%y %r") as created_at FROM users u INNER join roles r on r.id = u.roleID');

            return response()->json(
                [
                    'data' => $data,
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => $th,
                ],
                500
            );
        }
    }


    public function signIn(Request $request)
    {
        try {
            $credentials = array(
                'username' => $request->username,
                'password' => $request->password
            );

            if (Auth::attempt($credentials)) {
                $accesstoken = hash('sha256', $plainTextToken = Str::random(40));
                //Session Timer
                $expires = date('Y-m-d H:i:s', strtotime('+10 hours'));

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
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Could not connect to Server, Please Try again later',
                ],
                500
            );
        }
    }

    public function fetchuser(Request $request)
    {
        $token = $request->token;
        $role = $request->role;


        $datetime = date('Y-m-d H:i:s');
        //check first if user is authenticated
        $user = DB::select('SELECT u.*, r.roles FROM `users` u INNER JOIN roles r WHERE u.id in 
        ( select userID from accesstokens where token ="' . $token . '" and roleID=' . $role . ' )');

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
        try {
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
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Invalid',
                    'error'  => $th,
                ],
                401
            );
        }
    }

    public function update(Request $request)
    {
        try {
            $id      = $request->id;
            $name    = $request->name;
            $username = $request->username;
            $pass    = $request->pass;

            if ($pass == null) {
                User::findorFail($id)->update([
                    'name' => $name,
                    'username' => $username,
                ]);
            } else {
                User::findorFail($id)->update([
                    'name' => $name,
                    'username' => $username,
                    'password' =>  Hash::make($pass)
                ]);
            }
            return response()->json(
                [
                    'message' => 'Updated Successfully!',
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => $th,
                ],
                500
            );
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            User::findorFail($id)->delete();

            return response()->json(
                [
                    'message' => 'Deleted Successfully!',
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => $th,
                ],
                500
            );
        }
    }

    public function changepass(Request $request)
    {

        $id = $request->id;
        $oldpass = $request->oldpass;
        $newpass = $request->newpass;
        $repass  = $request->repass;
        $username = $request->username;


        $credentials = array(
            'username' => $username,
            'password' => $oldpass
        );


        if (Auth::attempt($credentials)) {
            if ($newpass == $repass) {

                User::findorFail($id)->update([
                    'password' => Hash::make($newpass)
                ]);

                return response()->json(
                    [
                        'response' => 'match',
                        'message' => 'Password Changed Successfully!',
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'response' => 'unmatch',
                        'message' => 'Password Does not Match',
                    ],
                    200
                );
            }
        } else {
            return response()->json(
                [
                    'response' => 'notmatchtodefault',
                    'message' => 'Password Does not Match',
                ],
                200
            );
        }
    }

    public function changename(Request $request)
    {
        $id = $request->id;
        $name = $request->name;

        $save = User::findorFail($id)->update([
            'name' => $name
        ]);

        if ($save) {
            return response()->json(
                [

                    'message' => 'Name Changed Successfully',
                ],
                200
            );
        }
    }
}
