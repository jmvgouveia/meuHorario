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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_schoolyear')->constrained('schoolyears')->onDelete('cascade');
            $table->foreignId('id_timeperiod')->constrained('timeperiod')->onDelete('cascade');
            $table->foreignId('id_room')->constrained('room')->onDelete('cascade');
            $table->foreignId('id_teacher')->constrained('teacher')->onDelete('cascade');
            $table->foreignId('id_weekday')->constrained('weekday')->onDelete('cascade');

            // $table->foreignId(TimePeriod::class);
            // $table->foreignId(Teacher::class);

            // $table->foreignId(Room::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
