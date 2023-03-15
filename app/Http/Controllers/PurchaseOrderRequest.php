<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PO;
use App\Models\Transaction;
use App\Models\AuditLogs;


class PurchaseOrderRequest extends Controller
{

    public function index()
    {

        $data = DB::connection("sqlsrv")->SELECT(" SELECT  dbo.udf_GetFullName(a.FK_faVendors) AS supplier,
        cat.description as category, a.docno AS PONo,
         a.docdate AS PODate, c.PK_iwItems AS ItemId, 
         c.itemdesc, b.price, b.qty, b.unit, b.qty * b.price AS Amount, 
         a.netcurramt AS totAmount, a.remarks, 
        a.PK_TRXNO, d.fullname, d.prcontactperson,
         a.vatamt, a.vatincl, d.praddress, d.prtelno, d.prfaxno,
          d.telefax, d.mobilephone, 'PR' + '-' + CONVERT(nvarchar, ISNULL(b.reqno, 0)) AS ReqNo, 
          b.conversion, e.description AS Terms, 
        dbo.faVendors.tinno, b.itemSpec, dbo.iwPRinv.seriesNo, 
        a.itbno, b.FK_mscProcurementList, z.PK_mscProcurementList, 
        z.description
FROM    dbo.iwPOinv AS a INNER JOIN
        dbo.iwPOitem AS b ON b.FK_TRXNO = a.PK_TRXNO INNER JOIN
        dbo.iwItems AS c ON b.FK_iwItems = c.PK_iwItems INNER JOIN
        dbo.psDatacenter AS d ON a.FK_faVendors = d.PK_psDatacenter INNER JOIN
        dbo.mscTerms AS e ON a.FK_mscTerms = e.PK_mscTerms INNER JOIN
        dbo.faVendors ON d.PK_psDatacenter = dbo.faVendors.PK_faVendors LEFT OUTER JOIN
        dbo.iwPRinv ON d.PK_psDatacenter = dbo.iwPRinv.FK_psDatacenter INNER JOIN
        dbo.mscProcurementList AS z ON b.FK_mscProcurementList = z.PK_mscProcurementList 
        LEFT JOIN mscPHICCategory cat on c.FK_mscPHICCategory = cat.PK_mscPHICCategory  order by a.docno asc");

        $curr = PO::orderBy('created_at', 'desc')->get();
        $POTransaction = Transaction::all();

        if (count($curr) >= 1) {
            if (count($data) == count($curr)) {
                return response()->json(
                    [
                        'data' => $curr,
                        'trans' => $POTransaction,
                    ],
                    200
                );
            } else {
                // UPDATE DATA IF DETECTED
                $datenow = date('Y-m-d');

                $batch = DB::select('SELECT max(batch) as batch FROM `p_o_s` ');
                $checkbatch = DB::select('SELECT max(batch) as batch FROM `p_o_s` where DATE(created_at) = "' . $datenow . '" ');
                if ($checkbatch[0]->batch == NULL) {
                    $newBatch = $batch[0]->batch + 1;
                } else {
                    $newBatch = $batch[0]->batch;
                }

                $allnew = [];
                $count  = 0;
                foreach ($data as $value) {
                    /* CHECKING NEW DATA */
                    $validate = PO::where('Amount', $value->Amount)
                        ->where('FK_mscProcurementList', $value->FK_mscProcurementList)
                        ->where('ItemId', $value->ItemId)
                        ->where('PK_TRXNO', $value->PK_TRXNO)
                        ->where('PK_mscProcurementList', $value->PK_mscProcurementList)
                        ->where('PODate', $value->PODate)
                        ->where('PONo', $value->PONo)
                        ->where('ReqNo', $value->ReqNo)
                        ->where('Terms', $value->Terms)
                        ->where('category', $value->category)
                        ->where('conversion', $value->conversion)
                        ->where('description', $value->description)
                        ->where('fullname', $value->fullname)
                        ->where('itbno', $value->itbno)
                        ->where('itemSpec', $value->itemSpec)
                        ->where('itemdesc', $value->itemdesc)
                        ->where('mobilephone', $value->mobilephone)
                        ->where('praddress', $value->praddress)
                        ->where('prcontactperson', $value->prcontactperson)
                        ->where('prfaxno', $value->prfaxno)
                        ->where('price', $value->price)
                        ->where('prtelno', $value->prtelno)
                        ->where('qty', $value->qty)
                        ->where('remarks', $value->remarks)
                        ->where('seriesNo', $value->seriesNo)
                        ->where('supplier', $value->supplier)
                        ->where('telefax', $value->telefax)
                        ->where('tinno', $value->tinno)
                        ->where('totAmount', $value->totAmount)
                        ->where('unit', $value->unit)
                        ->where('vatamt', $value->vatamt)
                        ->where('vatincl', $value->vatincl)->get();


                    if (count($validate) == 0) {
                        /* UPDATE TO LATEST */
                        array_push($allnew, $value);
                    }
                }
                foreach ($allnew as $value) {
                    $count++;
                    PO::create([
                        'Amount' => $value->Amount,
                        'FK_mscProcurementList' => $value->FK_mscProcurementList,
                        'ItemId' => $value->ItemId,
                        'PK_TRXNO' => $value->PK_TRXNO,
                        'PK_mscProcurementList' => $value->PK_mscProcurementList,
                        'PODate' => $value->PODate,
                        'PONo' => $value->PONo,
                        'ReqNo' => $value->ReqNo,
                        'Terms' => $value->Terms,
                        'category' => $value->category,
                        'conversion' => $value->conversion,
                        'description' => $value->description,
                        'fullname' => $value->fullname,
                        'itbno' => $value->itbno,
                        'itemSpec' => $value->itemSpec,
                        'itemdesc' => $value->itemdesc,
                        'mobilephone' => $value->mobilephone,
                        'praddress' => $value->praddress,
                        'prcontactperson' => $value->prcontactperson,
                        'prfaxno' => $value->prfaxno,
                        'price' => $value->price,
                        'prtelno' => $value->prtelno,
                        'qty' => $value->qty,
                        'remarks' => $value->remarks,
                        'seriesNo' => $value->seriesNo,
                        'supplier' => $value->supplier,
                        'telefax' => $value->telefax,
                        'tinno' => $value->tinno,
                        'totAmount' => $value->totAmount,
                        'unit' => $value->unit,
                        'vatamt' => $value->vatamt,
                        'vatincl' => $value->vatincl,
                        'batch' => $newBatch,
                        'newtag' => 1
                    ]);
                }

                if (count($allnew) == $count) {
                    return response()->json(
                        [
                            'data' => $curr,
                            'refresh' => 1,
                            'trans' => $POTransaction,
                        ],
                        200
                    );
                }
            }
        } else {

            /* 
                First Fetch
            
            */

            foreach ($data as $value) {

                PO::create([
                    'Amount' => $value->Amount,
                    'FK_mscProcurementList' => $value->FK_mscProcurementList,
                    'ItemId' => $value->ItemId,
                    'PK_TRXNO' => $value->PK_TRXNO,
                    'PK_mscProcurementList' => $value->PK_mscProcurementList,
                    'PODate' => $value->PODate,
                    'PONo' => $value->PONo,
                    'ReqNo' => $value->ReqNo,
                    'Terms' => $value->Terms,
                    'category' => $value->category,
                    'conversion' => $value->conversion,
                    'description' => $value->description,
                    'fullname' => $value->fullname,
                    'itbno' => $value->itbno,
                    'itemSpec' => $value->itemSpec,
                    'itemdesc' => $value->itemdesc,
                    'mobilephone' => $value->mobilephone,
                    'praddress' => $value->praddress,
                    'prcontactperson' => $value->prcontactperson,
                    'prfaxno' => $value->prfaxno,
                    'price' => $value->price,
                    'prtelno' => $value->prtelno,
                    'qty' => $value->qty,
                    'remarks' => $value->remarks,
                    'seriesNo' => $value->seriesNo,
                    'supplier' => $value->supplier,
                    'telefax' => $value->telefax,
                    'tinno' => $value->tinno,
                    'totAmount' => $value->totAmount,
                    'unit' => $value->unit,
                    'vatamt' => $value->vatamt,
                    'vatincl' => $value->vatincl,
                    'batch' => 1,
                    'newtag' => 0
                ]);
            }
        }
    }


    public function fetchsort(Request $request)
    {
        $data = $request->data;
        $supplier = '';
        $category = '';
        $unit     = '';
        $result   = [];


        $emailed = '';
        $delivered = '';
        $completed = '';
        $due = '';

        $extended = false;



        foreach ($data as $key => $value) {

            switch ($value['labelled']) {
                case 'Supplier':
                    $supplier = $value['value'];
                    break;
                case 'Category':
                    $category = $value['value'];
                    break;
                case 'Units':
                    $unit      = $value['value'];
                    break;
                case 'Emailed Date':
                    $emailed = $value['value'];
                    break;
                case 'Delivered Date':
                    $delivered = $value['value'];
                    break;
                case 'Completed Date':
                    $completed = $value['value'];
                    break;
                case 'Due Date':
                    $due = $value['value'];
                    break;

                case 'Extension':
                    $extended = true;
                    break;
            }
        }

        if ($extended) {
            $result = DB::select(' SELECT * FROM `p_o_s` where PK_posID in (select FK_poID from transactions 
            where extendedCount >=1 )');
        }



        if ($supplier != '' && $category != '' && $unit != '') {
            $result = PO::where('category', $category)
                ->where('supplier', $supplier)
                ->where('unit', $unit)->get();
        } else if ($supplier == '' && $category != '' && $unit != '') {
            $result = PO::where('category', $category)
                ->where('unit', $unit)->get();
        } else if ($supplier != '' && $category == '' && $unit != '') {
            $result = PO::where('supplier', $supplier)
                ->where('unit', $unit)->get();
        } else if ($supplier != '' && $category != '' && $unit == '') {
            $result = PO::where('category', $category)
                ->where('supplier', $supplier)
                ->get();
        } else if ($supplier != '' && $category == '' && $unit == '') {
            $result = PO::where('supplier', $supplier)
                ->get();
        } else if ($supplier == '' && $category != '' && $unit == '') {
            $result = PO::where('category', $category)
                ->get();
        } else if ($supplier == '' && $category == '' && $unit != '') {
            $result = PO::where('unit', $unit)->get();
        }


        if ($emailed != '' && $delivered != '' && $completed != '' && $due != '') {
            //Check all
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and delivered_date = "' . $delivered . '"
            and DueDate = "' . $due . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($delivered != '' && $completed != '' && $due != '' && $emailed == '') {
            // delivered,completed,due,

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where  delivered_date = "' . $delivered . '"
            and DueDate = "' . $due . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($completed != '' && $due != '' && $emailed != '' && $delivered == '') {
            // completed,due,email,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and DueDate = "' . $due . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($due != '' && $emailed != '' && $delivered != '' && $completed == '') {
            // due,email,delivered,

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and delivered_date = "' . $delivered . '"
            and DueDate = "' . $due . '"
            ) ');
        } else if ($emailed != '' && $delivered != '' && $completed != '' && $due == '') {
            // email,delivered,completed

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and delivered_date = "' . $delivered . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($emailed != '' && $delivered != '' && $due == '' && $completed == '') {
            //  email,delivered,

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and delivered_date = "' . $delivered . '"
            ) ');
        } else if ($completed != '' && $due != '' && $delivered == '' && $emailed == '') {
            // completed,due,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where DueDate = "' . $due . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($delivered != '' && $completed != '' && $due == '' && $delivered == '') {
            //delivered,completed,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where delivered_date = "' . $delivered . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($due != '' && $emailed != '' && $completed == '' && $delivered == '') {
            //due,email,

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and DueDate = "' . $due . '"
            ) ');
        } else if ($delivered != '' && $due != '' && $completed == '' && $emailed == '') {
            //delivered,due

            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where delivered_date = "' . $delivered . '"
            and DueDate = "' . $due . '"
            ) ');
        } else if ($emailed != '' && $completed != '' && $due == '' && $delivered == '') {
            // email,completed,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            and completed_date = "' . $completed . '"
            ) ');
        } else if ($emailed != '' && $delivered == '' && $due == '' && $delivered == '') {
            //emailed ,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where emailed_date ="' . $emailed . '"
            ) ');
        } else if ($delivered != '' && $emailed == '' && $due == '' && $completed == '') {
            // delivered,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where  delivered_date = "' . $delivered . '"
            ) ');
        } else if ($completed != '' && $due == '' && $delivered == '' && $emailed == '') {
            // completed,
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where completed_date = "' . $completed . '"
            ) ');
        } else if ($due != '' && $completed == '' && $delivered == '' && $emailed == '') {
            // due
            $result = DB::select('SELECT * FROM `p_o_s` where PK_posID in 
            (select FK_poID from transactions 
            where DueDate = "' . $due . '"   
            ) ');
        }

        return response()->json(
            [
                'data' => $result,
            ],
            200
        );
    }

    public function setviewed(Request $request)
    {
        $data = $request->data;

        $loguser = DB::select('select * from users where id  in (SELECT userID FROM `accesstokens` where token = "' . $request->token . '" )');
        AuditLogs::create([
            "username" => $loguser[0]->username,
            "actiontype" => "Viewed Items : " . count($data['selection'])
        ]);

        foreach ($data['selection'] as $row) {
            $id = $row['id'];
            PO::where('PK_posID', $id)->update([
                'newtag' => 0
            ]);


            $check = Transaction::where('FK_PoID', $id);
            $datetime = date('Y-m-d H:i:s');

            if (count($check->get()) == 0) {
                Transaction::create([
                    'FK_PoID' => $id,
                    'extendedCount' => 0,
                    'duration_date' => null,
                    'emailed_date' => null,
                    'received_date' => null,
                    'delivered_date' => null,
                    'completed_date' => null,
                    'DueDate' => null,
                    'confirmation' => 0,
                    'confirmedby' => 0,
                    'status' => 0,
                    'remarks' => null
                ]);
            } else {
                $check->update([
                    'updated_at' => $datetime
                ]);
            }
        }
    }
}
