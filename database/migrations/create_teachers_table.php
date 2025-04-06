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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->integer('teachernumber')->unique();
            $table->string('name');
            $table->string('acronym');
            $table->date('birthdate');
            $table->date('startingdate');
            $table->foreignId('id_nationality')->constrained('nationalitys')->onDelete('cascade');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_gender')->constrained('genders')->onDelete('cascade');
            $table->foreignId('id_qualifications')->constrained('qualifications')->onDelete('cascade');
            $table->foreignId('id_department')->constrained('departments')->onDelete('cascade');
            $table->foreignId('id_professionalrelationship')->constrained('professional_relationships')->onDelete('cascade');
            $table->foreignId('id_contractualrelationship')->constrained('contratual_relationships')->onDelete('cascade');
            $table->foreignId('id_salaryscale')->constrained('salary_scales')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');

    }
};
