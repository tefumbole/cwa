<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressCityStateToCwaMembers extends Migration
{
    public function up()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            if (! Schema::hasColumn('cwa_members', 'address')) {
                $table->string('address', 180)->nullable()->after('country');
            }
            if (! Schema::hasColumn('cwa_members', 'city')) {
                $table->string('city', 80)->nullable()->after('address');
            }
            if (! Schema::hasColumn('cwa_members', 'state')) {
                $table->string('state', 80)->nullable()->after('city');
            }
        });
    }

    public function down()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            foreach (['address', 'city', 'state'] as $col) {
                if (Schema::hasColumn('cwa_members', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
