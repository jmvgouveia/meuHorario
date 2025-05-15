<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_schedule_conflict')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('id_teacher_requester')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('id_schedule_novo')->nullable()->constrained('schedules')->onDelete('set null');

            $table->text('justification');
            $table->enum('status', [
                'Pendente',
                'Recusado',
                'Aprovado Professor',
                'Escalado',
                'Aprovado Coordenador',
            ])->default('Pendente');

            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_requests');
    }
};
