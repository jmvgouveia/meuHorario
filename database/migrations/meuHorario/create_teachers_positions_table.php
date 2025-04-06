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
        Schema::create('teachers_positions', function (Blueprint $table) {
            $table->foreignId('id')->constrained('positions')->onDelete('cascade');
            $table->foreignId('id')->constrained('teachers')->onDelete('cascade');
            $table->primary(['id', 'id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers_positions');

    }
};
