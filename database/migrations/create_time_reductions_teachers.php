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
        Schema::create('time_reduction_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->onDelete('cascade');
            $table->foreignId('id_time_reduction')
                ->constrained('time_reductions')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_reduction_teachers');

    }
};
