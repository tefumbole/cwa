<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWizardFieldsToCwaMembers extends Migration
{
    public function up()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            if (! Schema::hasColumn('cwa_members', 'country_code')) {
                $table->string('country_code', 8)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('cwa_members', 'whatsapp_phone')) {
                $table->string('whatsapp_phone', 30)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('cwa_members', 'id_type')) {
                $table->string('id_type', 20)->nullable()->after('id_back_path');
            }
            if (! Schema::hasColumn('cwa_members', 'id_issue_date')) {
                $table->string('id_issue_date', 40)->nullable()->after('id_type');
            }
            if (! Schema::hasColumn('cwa_members', 'id_issue_place')) {
                $table->string('id_issue_place', 120)->nullable()->after('id_issue_date');
            }
        });
    }

    public function down()
    {
        Schema::table('cwa_members', function (Blueprint $table) {
            foreach (['country_code', 'whatsapp_phone', 'id_type', 'id_issue_date', 'id_issue_place'] as $col) {
                if (Schema::hasColumn('cwa_members', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
