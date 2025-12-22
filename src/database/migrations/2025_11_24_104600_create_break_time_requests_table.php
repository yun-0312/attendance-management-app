<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_time_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')->constrained('attendance_requests')->onDelete('cascade');
            $table->dateTime('requested_break_start');
            $table->dateTime('requested_break_end');
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
        Schema::dropIfExists('break_time_requests');
    }
}