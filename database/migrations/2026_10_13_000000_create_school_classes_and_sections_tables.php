<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classes and Sections are now managed dynamically from the admin panel
 * instead of being hard-coded in config. Each Section maps to exactly one
 * Google Sheets worksheet (tab) named "<Class> - <Section>", e.g.
 * "Class 1 - A".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();

            // Name of the Google Sheets worksheet this section maps to, as last
            // synchronised. Kept so a rename can be applied safely.
            $table->string('worksheet')->nullable();

            // Google Sheets synchronisation bookkeeping.
            $table->string('google_sync_status')->default('pending');
            $table->text('google_sync_error')->nullable();
            $table->timestamp('google_synced_at')->nullable();

            $table->timestamps();

            // A section name must be unique within its class.
            $table->unique(['school_class_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
        Schema::dropIfExists('school_classes');
    }
};
