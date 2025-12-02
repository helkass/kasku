<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTransactionIdOnTableFinanceHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('finance_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_histories', function (Blueprint $table) {
            $table->dropColumn(['transaction_id']);
        });
    }
}
