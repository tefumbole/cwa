<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSelfieToUsers extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'selfie')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('selfie')->nullable();
        });
    }

    public function down()
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'selfie')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('selfie');
        });
    }
}
