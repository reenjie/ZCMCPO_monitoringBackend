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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('PK_transID');
            $table->integer('FK_PoID');
            $table->integer('extendedCount');
            $table->date('duration_date')->nullable();
            $table->date('emailed_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->date('cancelled_date')->nullable();
            $table->date('DueDate')->nullable();
            $table->integer('status')->comment('1 = undelivered , 2= delivered , 3=cancelled , 4=completed ');
            $table->text('requestby')->comment('MMSUser username');
            $table->integer('confirmation')->comment('0 = default, 1=toconfirm, 2 = confirmed');
            $table->integer('confirmedby')->comment('Supervisor Account ID');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('transactions');
    }
};
