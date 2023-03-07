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
        $datenow = date('Y-m-d');

        switch ($typeofaction) {
            case 'cancel':
                $data->update([
                    'status' => 3,
                    'cancelled_date' => date('Y-m-d')
                ]);
                break;
            case 'undeliver':
                $data->update([
                    'status' => 1
                ]);
                break;
            case 'extend':
                $addedcount = $request->extendedCount + 1;

                $terms = $request->terms;
                $due   = $request->due;
                if ($terms == "default") {
                    $defterms = "15";
                } else {
                    $defterms = $terms;
                }

                $wterms = date('Y-m-d', strtotime($due . '+' . $defterms . ' days'));
                $data->update([
                    'extendedCount' => $addedcount,
                    'duration_date' => $wterms
                ]);
                break;
            case 'deliver':
                $data->update([
                    'status' => 2,
                    'delivered_date' => $datenow,


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

    public function setEmailedDate(Request $request)
    {
        $emDate = $request->emDate;
        $id     = $request->id;
        $terms  = $request->terms;


        if ($terms == "default") {
            $defterms = "15";
        } else {
            $defterms = $terms;
        }

        $wterms = date('Y-m-d', strtotime($emDate . '+' . $defterms . ' days'));


        Transaction::where('FK_PoID', $id)->update([
            'emailed_date' => $emDate,
            'DueDate' => $wterms
        ]);
    }

    public function UndoAction(Request $request)
    {
        $id = $request->id;
        Transaction::where('FK_PoID', $id)->update([
            'delivered_date' => null,
            'status' => 0
        ]);
    }
}
