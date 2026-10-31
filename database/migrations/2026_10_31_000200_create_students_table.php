<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->nullable()
                ->constrained('school_classes')
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->nullOnDelete();

            // The 30 admission fields (Class and Section are stored above).
            $table->string('admission_number', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('gender', 20);
            $table->string('date_of_birth', 20);
            $table->string('mobile', 30);
            $table->string('roll_no', 50)->nullable();
            $table->string('guardian_email', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('admission_date', 20)->nullable();
            $table->string('blood_group', 20)->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('height', 20)->nullable();
            $table->string('weight', 20)->nullable();
            $table->string('father_name', 150)->nullable();
            $table->string('father_phone', 30)->nullable();
            $table->string('father_occupation', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('mother_phone', 30)->nullable();
            $table->string('mother_occupation', 150)->nullable();
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_relation', 100)->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_occupation', 150)->nullable();
            $table->text('current_address')->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('national_identification_no', 60)->nullable();
            $table->text('previous_school_details')->nullable();
            $table->text('note')->nullable();

            // Idempotency key for the final submission step.
            $table->string('submission_token', 64)->nullable()->unique();

            $table->timestamps();

            $table->index(['school_id', 'class_id', 'section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
