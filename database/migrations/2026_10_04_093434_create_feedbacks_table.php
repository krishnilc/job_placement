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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('given_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('given_to')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('feedback_type', ['employer_to_student', 'student_to_employer']);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['job_application_id', 'feedback_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
