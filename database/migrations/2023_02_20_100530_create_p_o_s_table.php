<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_o_s', function (Blueprint $table) {
            $table->id('PK_posID');
            $table->text('Amount')->nullable();
            $table->text('FK_mscProcurementList')->nullable();
            $table->text('ItemId')->nullable();
            $table->integer('PK_TRXNO')->nullable();
            $table->text('PK_mscProcurementList')->nullable();
            $table->dateTime('PODate')->nullable();
            $table->integer('PONo')->nullable();
            $table->text('ReqNo')->nullable();
            $table->text('Terms')->nullable();
            $table->text('category')->nullable();
            $table->text('conversion')->nullable();
            $table->text('description')->nullable();
            $table->text('fullname')->nullable();
            $table->text('itbno')->nullable();
            $table->text('itemSpec')->nullable();
            $table->text('itemdesc')->nullable();
            $table->text('mobilephone')->nullable();
            $table->text('praddress')->nullable();
            $table->text('prcontactperson')->nullable();
            $table->text('prfaxno')->nullable();
            $table->text('price')->nullable();
            $table->text('prtelno')->nullable();
            $table->text('qty')->nullable();
            $table->text('remarks')->nullable();
            $table->text('seriesNo')->nullable();
            $table->text('supplier')->nullable();
            $table->text('telefax')->nullable();
            $table->text('tinno')->nullable();
            $table->text('totAmount')->nullable();
            $table->text('unit')->nullable();
            $table->text('vatamt')->nullable();
            $table->integer('vatincl')->nullable();
            $table->integer('batch')->comment('batches when it was added');
            $table->integer('newtag')->default(0)->comment('0 = oldfile, 1 = newfile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('p_o_s');
    }
};
