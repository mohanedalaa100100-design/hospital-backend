<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFullNameToNameInUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'full_name');
        });
    }
}