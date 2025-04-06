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
        Schema::create('users_roles', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_role')->constrained('roles')->onDelete('cascade');
            $table->primary(['id_user', 'id_role']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_roles');

    }
};
