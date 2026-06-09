<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedTinyInteger('week_score')->default(0)->comment('AI score 0–100');
            $table->unsignedTinyInteger('engagement_score')->default(0)->comment('AI engagement 0–10');
            $table->unsignedTinyInteger('task_completion_rate')->default(0)->comment('0–100%');
            $table->string('overall_sentiment', 30)->default('neutral');
            $table->string('overall_rating', 60)->nullable();
            $table->json('report_json')->comment('Full AI report payload');
            $table->timestamps();

            // One report per intern per week (upsert strategy)
            $table->unique(['intern_id', 'week_start'], 'weekly_reports_intern_week_unique');
            $table->index('generated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
