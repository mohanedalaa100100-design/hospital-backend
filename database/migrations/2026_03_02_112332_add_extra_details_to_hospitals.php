<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
        
            $columns = ['rating', 'accreditation', 'whatsapp', 'working_hours', 'about'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('hospitals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('hospitals', function (Blueprint $table) {
        
            $table->string('rating')->default('4.2');
            $table->string('accreditation')->default('JCI Accredited');
            $table->string('whatsapp')->nullable();
            $table->string('working_hours')->default('Available 24/7');
            $table->text('about')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $columns = ['rating', 'accreditation', 'whatsapp', 'working_hours', 'about'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('hospitals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};