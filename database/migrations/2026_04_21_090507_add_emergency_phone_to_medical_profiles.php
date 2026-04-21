<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_profiles', function (Blueprint $table) {
            $table->string('emergency_phone')->nullable()->after('special_condition');
        });
    }

    public function down(): void
    {
        Schema::table('medical_profiles', function (Blueprint $table) {
            $table->dropColumn('emergency_phone');
        });
    }
};