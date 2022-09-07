<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cnae_file');
            $table->foreign('id_cnae_file')->references('id')->on('cnae_file')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('id_store');
            $table->foreign('id_store')->references('id')->on('store')->onUpdate('cascade')->onDelete('cascade');
            $table->string('type');
            $table->date('date');
            $table->double('value');
            $table->string('card');
            $table->string('hour');
            $table->double('balance_before_operation')->default(0);
            $table->double('balance_after_operation')->default(0);
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
        Schema::dropIfExists('transaction');
    }
}
