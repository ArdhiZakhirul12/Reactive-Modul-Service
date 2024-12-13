<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetailCustomerMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_customer_machines', function (Blueprint $table) {
            $table->uuid('id')->index()->primary();
            $table->foreignUuid('user_machine_id');
            $table->foreignUuid('category_id')->refrences('id')->on('category')->onDelete('cascade');
            $table->string('serial_number');
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('detail_customer_machines');
    }
}
