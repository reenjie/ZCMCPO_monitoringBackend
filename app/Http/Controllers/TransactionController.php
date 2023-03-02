<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function FetchRecent(Request $request)
    {
        $data = DB::select('SELECT * FROM `p_o_s` WHERE PK_posID in (select FK_PoID from `transactions` )');
        return response()->json(
            [
                'data' => $data,
            ],
            200
        );
    }

    public function FetchPOstatus(Request $request)
    {
        $data = Transaction::all();
        return response()->json(
            [
                'data' => $data,
            ],
            200
        );
    }
}
