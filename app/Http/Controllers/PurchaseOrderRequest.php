<?php

namespace App\Http\Controllers;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PO;

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

        if (count($curr) >= 1) {
            if (count($data) == count($curr)) {
                return response()->json(
                    [
                        'data' => $curr,
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
                        $update = PO::create([
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
                        if ($update) {
                            return response()->json(
                                [
                                    'data' => $curr,
                                ],
                                200
                            );
                        }
                    } else {
                        return response()->json(
                            [
                                'data' => $curr,
                            ],
                            200
                        );
                    }
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
            }
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
        // echo $supplier . $category . $unit;
        //$result = PO::where('supplier',)

        return response()->json(
            [
                'data' => $result,
            ],
            200
        );
    }
}
