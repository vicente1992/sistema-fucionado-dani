<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('income_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('id_user_agent')->nullable()->default(null);
            $table->integer('id_supervisor')->nullable()->default(null);
            $table->float('base')->nullable()->default('0');
            $table->float('base_current')->nullable()->default('0');
            $table->float('base_total')->nullable()->default('0');
            $table->integer('id_wallet')->nullable()->default(null);
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
        Schema::dropIfExists('income_history');
    }
}
