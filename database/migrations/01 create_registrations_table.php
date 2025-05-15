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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_student')->constrained('students')->onDelete('cascade');
            $table->foreignId('id_course')->constrained('courses')->onDelete('cascade');
            $table->foreignId('id_schoolyear')->constrained('schoolyears')->onDelete('cascade');
            $table->foreignId('id_class')->constrained('classes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
