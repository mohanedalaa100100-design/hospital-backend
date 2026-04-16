<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameFullNameToNameInUsersTable extends Migration
{
    public function up()
    {
        
        if (Schema::hasColumn('users', 'full_name')) {
            Schema::table('users', function (Blueprint $table) {
                
                DB::statement('ALTER TABLE users CHANGE full_name name VARCHAR(255)');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                DB::statement('ALTER TABLE users CHANGE name full_name VARCHAR(255)');
            });
        }
    }
}