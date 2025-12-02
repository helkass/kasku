<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_id');
            $table->double('amount');
            $table->unsignedBigInteger('created_by');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('finance_id')->references('id')->on('finances')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finance_histories');
    }
}
