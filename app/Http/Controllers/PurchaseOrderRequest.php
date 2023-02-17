<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRequest extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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



        return response()->json(
            [
                'data' => $data,

            ],
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
