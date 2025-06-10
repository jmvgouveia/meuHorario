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
        Schema::create('time_reductions', function (Blueprint $table) {
            $table->id();
            $table->string('time_reduction');
            $table->string('time_reduction_description');
            $table->integer('time_reduction_value');
            $table->integer('time_reduction_value_nl'); // 'fixed' or 'percentage'
            $table->enum('time_reduction_type', ['fixed', 'percentage'])->default('fixed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_reductions');
    }
};
