<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;

class RoleController extends Controller
{
    public function index(Request $request)
    {

        $data = Roles::all();

        return response()->json(
            [
                'data' => $data,

            ],
            200
        );
    }
}
