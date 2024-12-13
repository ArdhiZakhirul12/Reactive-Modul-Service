<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddresType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('address_type')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('user_addresses', function (Blueprint $table) {
        //     $table->dropColumn('address_type');
        //     $table->dropColumn('contact_person_name');
        //     $table->dropColumn('contact_person_number');
        // });
        Schema::dropIfExists('user_addresses');
    }
}
