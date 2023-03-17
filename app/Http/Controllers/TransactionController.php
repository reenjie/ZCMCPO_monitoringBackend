<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\AuditLogs;
use App\Models\PO;
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
        $data_ = $request->data;
        $id = $data_['id'];
        $typeofaction = $data_['typeofaction'];
        $data = Transaction::where('FK_PoID', $id);
        $datenow = date('Y-m-d');

        $po = PO::where('PK_posID', $id)->get();
        $action = '';

        switch ($typeofaction) {
            case 'cancel':
                $data->update([
                    'status' => 3,
                    'cancelled_date' => date('Y-m-d'),
                    'remarks' => "Cancelled"
                ]);
                $action = "Items Marked Cancelled   | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc;
                break;
            case 'undeliver':
                $data->update([
                    'status' => 1,
                    'remarks' => "Undelivered"
                ]);

                $action = "Items Marked Undelivered  | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc;
                break;
            case 'extend':
                $addedcount = $data_['extendedCount'] + 1;

                $terms = $data_['terms'];
                $due   = $data_['due'];
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

                $action = "Items DueDate Extended | New DueDate: " . $wterms . "  | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc;
                break;
        }

        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => $action
        ]);
    }

    public function setEmailedDate(Request $request)
    {
        $data = $request->data;
        $emDate = $data['emDate'];
        $id     = $data['id'];
        $terms  = $data['terms'];

        $po = PO::where('PK_posID', $id)->get();

        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => "Set emailed date : " . $emDate . " &&  PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
        ]);

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
        $data = $request->data;
        $id = $data['id'];
        $untype = $data['untype'];


        $po = PO::where('PK_posID', $id)->get();
        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => "Request Confirmation for Undoing Action || PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
        ]);



        Transaction::where('FK_PoID', $id)->update([
            'confirmation' => 1,
            'requestby' => $loguser[0]->username,
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
        $data = $request->data;
        $id = $data['id'];
        $dates = $data['dates'];
        $entity = $data['entity'];
        if ($entity == "remarks") {
            $po = PO::where('PK_posID', $id)->get();
            $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
            AuditLogs::create([
                "username" => $loguser[0]->username,
                "actiontype" => "Added Remarks : " . $dates . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
            ]);
        }
        DB::select('UPDATE `transactions` SET  `' . $entity . '`= "' . $dates . '" WHERE `FK_PoID` = ' . $id . ' ');
    }

    public function SetDeliveredDate(Request $request)
    {
        $data = $request->data;
        $id = $data['id'];
        $em = $data['em'];

        $po = PO::where('PK_posID', $id)->get();
        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => "Mark as Delivered | Date : " . $em . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
        ]);

        Transaction::where('FK_PoID', $id)->update([
            'status' => 2,
            'delivered_date' => $em,
            'remarks' => "Delivered"
        ]);
    }

    public function Applytoall(Request $request)
    {
        $item = $request->data;
        $data = $item['data'];
        $selection = $item['selection'];
        $ttype = $item['ttype'];
        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
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

                    $po = PO::where('PK_posID', $id)->get();
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" => "Set emailed date : " . $data . " &&  PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                    ]);
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
                    $po = PO::where('PK_posID', $id)->get();
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" =>   "Mark as Delivered | Date : " . $data . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                    ]);

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
                    $po = PO::where('PK_posID', $id)->get();
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" => "Items Marked Cancelled   | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                    ]);

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

                    $po = PO::where('PK_posID', $id)->get();
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" => "Items Marked Undelivered  | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                    ]);

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


                                $po = PO::where('PK_posID', $id)->get();
                                AuditLogs::create([
                                    "username" => $loguser[0]->username,
                                    "actiontype" => "Items DueDate Extended | New DueDate: " . $wterms . "  | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                                ]);
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

                                    $po = PO::where('PK_posID', $id)->get();
                                    AuditLogs::create([
                                        "username" => $loguser[0]->username,
                                        "actiontype" => "Items DueDate Extended | New DueDate: " . $wterms . "  | PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
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

                    $po = PO::where('PK_posID', $id)->get();
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" => "Added Remarks : " . $data . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
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

                            $po = PO::where('PK_posID', $id)->get();
                            AuditLogs::create([
                                "username" => $loguser[0]->username,
                                "actiontype" => "Mark as Completed | Date : " . date('Y-m-d') . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
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
                    $po = PO::where('PK_posID', $id)->get();
                    $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
                    AuditLogs::create([
                        "username" => $loguser[0]->username,
                        "actiontype" => "Request Confirmation for Undoing Action || PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
                    ]);
                    if ($check[0]->delivered_date != null || $check[0]->cancelled_date != null) {
                        Transaction::where('FK_PoID', $id)->update([
                            'confirmation' => 1,
                            'requestby' => $loguser[0]->username,
                        ]);
                    }

                    break;
            }
        }
    }

    public function MarkComplete(Request $request)
    {
        $data = $request->data;
        $id = $data['id'];
        $datenow = date('Y-m-d');

        $po = PO::where('PK_posID', $id)->get();
        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => "Mark as Completed | Date : " . $datenow . " : PO number : " . $po[0]->PONo . " && Item desc: " . $po[0]->itemdesc
        ]);

        Transaction::where('FK_PoID', $id)->update([
            'status' => 4,
            'completed_date' => $datenow,
            'remarks' => 'Completed'
        ]);
    }


    public function cardCount()
    {
        $undelivered = DB::select('SELECT * FROM `p_o_s` where PK_posID in ( SELECT FK_PoID FROM `transactions` where status = 1 ) ');
        $cancelled   = DB::select('SELECT * FROM `p_o_s` where PK_posID in (SELECT FK_PoID FROM `transactions` where cancelled_date is not null);');
        $extended   = DB::select('SELECT * FROM `p_o_s` where PK_posID in (SELECT FK_PoID FROM `transactions` where extendedCount >=1 )');
        $delivered   = DB::select('SELECT * FROM `p_o_s` where PK_posID in (SELECT FK_PoID FROM `transactions` where delivered_date is not null)');

        $data = [
            'undelivered' => $undelivered,
            'cancelled'   => $cancelled,
            'extended'    => $extended,
            'delivered'   => $delivered
        ];

        return response()->json(
            [
                'data' => $data
            ],
            200
        );
    }

    public function filterRecent(Request $request)
    {
        $data = $request->data;
        $filteredDate = $data['filterDate'];

        $allform = DB::select('SELECT * FROM `p_o_s` where PK_posID in ( select FK_PoID from transactions  where  DATE(updated_at) = "' . $filteredDate . '" ) ');

        return response()->json(
            [
                'data' => $allform
            ],
            200
        );
    }

    public function fetchForapproval(Request $request)
    {
        $data = DB::select('select t.requestby, p.PONo , p.itemdesc , p.PK_posID from transactions t INNER JOIN p_o_s p on t.FK_PoID = p.PK_posID where t.confirmation = 1 ');

        $po = DB::select('SELECT * FROM `p_o_s` where PK_posID in ( select FK_PoID from transactions  where  confirmation = 1) ');

        return response()->json(
            [
                'data' => $data,
                'po'   => $po
            ],
            200
        );
    }
}
