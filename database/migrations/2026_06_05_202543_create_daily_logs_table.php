<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->date('log_date');
            $table->boolean('is_present')->default(true);
            $table->text('daily_note')->nullable()
                ->comment('What the intern worked on that day — fed into the AI weekly report.');
            $table->timestamps();

            $table->unique(['intern_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
