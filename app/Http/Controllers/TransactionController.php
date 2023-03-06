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

    public function SetStatus(Request $request)
    {
        $id = $request->id;
        $typeofaction = $request->typeofaction;
        $data = Transaction::where('FK_PoID', $id);

        switch ($typeofaction) {
            case 'cancel':
                $data->update([
                    'status' => 3
                ]);
                break;
            case 'undeliver':
                $data->update([
                    'status' => 1
                ]);
                break;
            case 'extend':
                $data->update([
                    'status' => 5
                ]);
                break;
            case 'deliver':
                $data->update([
                    'status' => 2
                ]);
                break;
            case 'remarks':

                break;

            case 'Updateall':
                $selection = $request->selection;
                $selected  = $request->selected;
                foreach ($selection as $key => $value) {
                    $selId  = $value->id;

                    switch ($selected) {
                        case 'cancel':
                            # code...
                            break;
                    }

                    //    Transaction::where('FK_PoID',$selId)->update([

                    //    ]);

                }

                break;
        }
    }
}
