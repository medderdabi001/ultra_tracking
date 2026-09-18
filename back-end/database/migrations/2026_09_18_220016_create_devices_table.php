<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('imei')->unique();
            $table->unsignedBigInteger('traccar_device_id')->unique()->nullable();
            $table->string('model')->default('FMC920');
            $table->string('phone_number')->nullable();
            $table->string('protocol')->default('teltonika');
            $table->enum('status', ['active', 'inactive', 'faulty'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
