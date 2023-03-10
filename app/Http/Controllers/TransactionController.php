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

        Transaction::where('FK_PoID', $id)->update([
            'confirmation' => 1,
        ]);

        // if ($untype == "delivered") {
        //     Transaction::where('FK_PoID', $id)->update([
        //         'delivered_date' => null,
        //         'completed_date' => null,
        //         'status' => 0,
        //         'remarks' => null
        //     ]);
        // } else {
        //     Transaction::where('FK_PoID', $id)->update([
        //         'cancelled_date' => null,
        //         'completed_date' => null,
        //         'status' => 0,
        //         'remarks' => null
        //     ]);
        // }
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
        $data = $request->data;
        $selection = $request->selection;
        $ttype = $request->ttype;
        foreach ($selection as $key => $value) {
            $id =  $value['id'];
            $check = Transaction::where('FK_PoID', $id)->get();
            switch ($ttype) {
                case 'saveemail':
                    $terms = $value['data'][0]['Terms'];
                    if (preg_match('/(\d+)/', $terms, $matches)) {
                        $number = $matches[1];
                        $wterms = date('Y-m-d', strtotime($data . '+' . $number . ' days'));
                        if (count($check) >= 1) {
                            if (!$check[0]->emailed_date) {
                                Transaction::where('FK_PoID', $id)->update([
                                    'emailed_date' => $data,
                                    'DueDate' => $wterms
                                ]);
                            }
                        }
                    } else {
                        $wterms = date('Y-m-d', strtotime($data . '+15 days'));
                        if (count($check) >= 1) {
                            if (!$check[0]->emailed_date) {
                                Transaction::where('FK_PoID', $id)->update([
                                    'emailed_date' => $data,
                                    'DueDate' => $wterms
                                ]);
                            }
                        }
                    }
                    break;
                case 'savedelivered':
                    if ($check[0]->emailed_date) {
                        if (!$check[0]->delivered_date) {
                            if (!$check[0]->cancelled_date) {
                                Transaction::where('FK_PoID', $id)->update([
                                    'status' => 2,
                                    'delivered_date' => $data,
                                    'remarks' => "Delivered"
                                ]);
                            }
                        }
                    }

                    break;
                case 'savecancelled':


                    if (!$check[0]->cancelled_date) {

                        if (!$check[0]->delivered_date) {
                            Transaction::where('FK_PoID', $id)->update([
                                'status' => 3,
                                'cancelled_date' => date('Y-m-d'),
                                'remarks' => "Cancelled"
                            ]);
                        }
                    }


                    break;
                case 'saveundelivered':

                    if ($check[0]->emailed_date) {
                        if (!$check[0]->cancelled_date) {
                            if (!$check[0]->delivered_date) {
                                Transaction::where('FK_PoID', $id)->update([
                                    'status' => 1,
                                    'remarks' => "Undelivered"
                                ]);
                            }
                        }
                    }


                    break;
                case "saveExtend":
                    if ($check[0]->emailed_date) {
                        $terms = $value['data'][0]['Terms'];
                        $due   = $check[0]->DueDate;
                        $extended = $check[0]->duration_date;
                        $addedcount = $check[0]->extendedCount + 1;
                        $datenow = date('Y-m-d');


                        if (preg_match('/(\d+)/', $terms, $matches)) {
                            $number = $matches[1];
                            if (!$check[0]->duration_date) {
                                $wterms = date('Y-m-d', strtotime($due . '+' . $number . ' days'));
                            } else {
                                $wterms = date('Y-m-d', strtotime($extended . '+' . $number . ' days'));
                            }
                        } else {
                            if (!$check[0]->duration_date) {
                                $wterms = date('Y-m-d', strtotime($due . '+15 days'));
                            } else {
                                $wterms = date('Y-m-d', strtotime($extended . '+15 days'));
                            }
                        }


                        if (!$check[0]->duration_date) {
                            //duedate
                            if ($due < $datenow) {
                                //expired
                            } else {
                                if ($check[0]->delivered_date == null && $check[0]->cancelled_date == null && $check[0]->completed_date == null) {
                                    Transaction::where('FK_PoID', $id)->update([
                                        'extendedCount' => $addedcount,
                                        'duration_date' => $wterms
                                    ]);
                                }
                            }
                        } else {
                            //durationdate
                            if ($extended < $datenow) {
                                //expired

                            } else {
                                if ($check[0]->delivered_date == null && $check[0]->cancelled_date == null && $check[0]->completed_date == null) {
                                    Transaction::where('FK_PoID', $id)->update([
                                        'extendedCount' => $addedcount,
                                        'duration_date' => $wterms
                                    ]);
                                }
                            }
                        }
                    }

                    break;

                case 'saveRemarks':

                    Transaction::where('FK_PoID', $id)->update([
                        'remarks' => $data
                    ]);


                    break;

                case 'saveCompleted':
                    if ($check[0]->emailed_date) {
                        if ($check[0]->delivered_date) {
                            Transaction::where('FK_PoID', $id)->update([
                                'remarks' => "completed",
                                'status'  => 4,
                                'completed_date' => date('Y-m-d')
                            ]);
                        }
                    }

                    break;

                case 'saveUndo':
                    // Transaction::where('FK_PoID', $id)->update([
                    //     'cancelled_date' => null,
                    //     'completed_date' => null,
                    //     'delivered_date' => null,
                    //     'status' => 0,
                    //     'remarks' => null
                    // ]);


                    Transaction::where('FK_PoID', $id)->update([
                        'confirmation' => 1,
                    ]);
                    break;
            }
        }
    }

    public function MarkComplete(Request $request)
    {
        $id = $request->id;
        $datenow = date('Y-m-d');

        Transaction::where('FK_PoID', $id)->update([
            'status' => 4,
            'completed_date' => $datenow,
            'remarks' => 'Completed'
        ]);
    }
}
