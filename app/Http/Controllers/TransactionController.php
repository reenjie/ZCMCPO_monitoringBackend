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
                    'cancelled_date' => date('Y-m-d'),
                    'remarks' => "Cancelled"
                ]);
                break;
            case 'undeliver':
                $data->update([
                    'status' => 1,
                    'remarks' => "Undelivered"
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
        $untype = $request->untype;
        if ($untype == "delivered") {
            Transaction::where('FK_PoID', $id)->update([
                'delivered_date' => null,
                'status' => 0,
                'remarks' => null
            ]);
        } else {
            Transaction::where('FK_PoID', $id)->update([
                'cancelled_date' => null,
                'status' => 0,
                'remarks' => null
            ]);
        }
    }

    public function UpdateDue(Request $request)
    {
        $id = $request->id;
        $dates = $request->dates;
        $entity = $request->entity;
        DB::select('UPDATE `transactions` SET  `' . $entity . '`= "' . $dates . '" WHERE `FK_PoID` = ' . $id . ' ');
    }

    public function SetDeliveredDate(Request $request)
    {
        $id = $request->id;
        $em = $request->em;
        $datenow = date('Y-m-d');

        Transaction::where('FK_PoID', $id)->update([
            'status' => 2,
            'delivered_date' => $datenow,
            'remarks' => "Delivered"
        ]);
    }

    public function Applytoall(Request $request)
    {
        $emaildate = $request->emaildate;
        $selection = $request->selection;

        foreach ($selection as $key => $value) {
            $id =  $value['id'];
            $terms = $value['data'][0]['Terms'];


            if (preg_match('/(\d+)/', $terms, $matches)) {
                $number = $matches[1];
                echo $number;
            } else {
                echo 'by default : 15';
            }
        }
    }
}
