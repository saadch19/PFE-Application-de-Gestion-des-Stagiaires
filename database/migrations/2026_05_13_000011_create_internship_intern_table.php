<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_intern', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_id')->constrained('internships')->cascadeOnDelete();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['internship_id', 'intern_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_intern');
    }
};
