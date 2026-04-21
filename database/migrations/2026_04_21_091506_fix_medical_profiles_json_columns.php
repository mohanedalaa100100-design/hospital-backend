<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_profiles', function (Blueprint $table) {
            $table->json('chronic_diseases')->nullable()->change();
            $table->json('allergies')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('medical_profiles', function (Blueprint $table) {
            $table->text('chronic_diseases')->nullable()->change();
            $table->text('allergies')->nullable()->change();
        });
    }
};