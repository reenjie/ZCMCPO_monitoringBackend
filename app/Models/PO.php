<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PO extends Model
{
    use HasFactory;

    protected $fillable = [
        'Amount',
        'FK_mscProcurementList',
        'ItemId',
        'PK_TRXNO',
        'PK_mscProcurementList',
        'PODate',
        'PONo',
        'ReqNo',
        'Terms',
        'category',
        'conversion',
        'description',
        'fullname',
        'itbno',
        'itemSpec',
        'itemdesc',
        'mobilephone',
        'praddress',
        'prcontactperson',
        'prfaxno',
        'price',
        'prtelno',
        'qty',
        'remarks',
        'seriesNo',
        'supplier',
        'telefax',
        'tinno',
        'totAmount',
        'unit',
        'vatamt',
        'vatincl',
        'batch',
        'newtag'
    ];
}
