<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->index();
        });

        // Existing installations already have classes. Keep that data by
        // assigning it to a default school instead of deleting anything.
        if (DB::table('school_classes')->whereNull('school_id')->exists()) {
            $schoolId = DB::table('schools')->insertGetId([
                'name' => 'Default School',
                'slug' => 'default-school',
                'admission_token' => Str::random(48),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('school_classes')
                ->whereNull('school_id')
                ->update(['school_id' => $schoolId]);
        }

        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique('school_classes_name_unique');
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'name']);
            $table->unique('name');
            $table->dropColumn('school_id');
        });
    }
};
