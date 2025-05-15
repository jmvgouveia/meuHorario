<?php

use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SchoolYears;
use App\Models\Teacher;
use App\Models\TimePeriod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registrations_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_subject')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('id_registration')->constrained('registrations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations_subjects');
    }
};
