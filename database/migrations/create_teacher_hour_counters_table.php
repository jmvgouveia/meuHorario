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
        Schema::create('teacher_hour_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_teacher')
                ->constrained('teachers')
                ->onDelete('cascade');
            $table->integer('carga_horaria');
            $table->integer('carga_componente_letiva');
            $table->integer('carga_componente_naoletiva');
           $table->enum('autorizado_horas_extra', ['Autorizado', 'Nao_autorizado'])
    ->default('Nao_autorizado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_hour_counters');

    }
};
