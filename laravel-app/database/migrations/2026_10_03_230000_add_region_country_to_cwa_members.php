<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionCountryToCwaMembers extends Migration
{
    public function up()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            if (! Schema::hasColumn('cwa_members', 'region')) {
                $table->string('region', 80)->nullable()->after('parish');
            }
            if (! Schema::hasColumn('cwa_members', 'country')) {
                $table->string('country', 80)->nullable()->after('region');
            }
        });
    }

    public function down()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            foreach (['region', 'country'] as $col) {
                if (Schema::hasColumn('cwa_members', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
