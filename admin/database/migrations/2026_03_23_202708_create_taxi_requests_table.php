<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('taxi_requests')) {
            return;
        }
        
        Schema::create('taxi_requests', function (Blueprint $table): void {
            $table->increments('request_id');
            $table->string('customer_name', 255);
            $table->string('customer_phone', 20);
            $table->string('pickup_location', 255);
            $table->string('dropoff_location', 255);
            $table->dateTime('pickup_time');
            $table->integer('number_of_passengers');
            $table->text('special_requirements')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxi_requests');
    }
};
