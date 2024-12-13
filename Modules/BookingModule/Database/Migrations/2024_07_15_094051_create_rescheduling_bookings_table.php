<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReschedulingBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rescheduling_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('booking_id')->constrained();
            $table->foreignUuid('serviceman_id')->constrained()->nullable();
            $table->json('ongoing_photos')->nullable();
            $table->json('evidence_photos')->nullable();
            $table->string('serviceman_note')->nullable();
            $table->dateTime('service_schedule')->nullable();
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
        Schema::dropIfExists('rescheduling_bookings');
    }
}
