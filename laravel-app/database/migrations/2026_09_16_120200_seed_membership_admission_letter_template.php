<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedMembershipAdmissionLetterTemplate extends Migration
{
    const TEMPLATE_NAME = 'Letter of Admission';

    public function up()
    {
        if (! Schema::hasTable('letter_templates') || ! Schema::hasTable('letter_categories')) {
            return;
        }

        $categoryId = DB::table('letter_categories')->where('name', 'Membership')->value('id');
        if (! $categoryId) {
            $categoryId = DB::table('letter_categories')->insertGetId([
                'name' => 'Membership',
                'is_active' => 1,
            ]);
        }

        if (DB::table('letter_templates')->where('name', self::TEMPLATE_NAME)->exists()) {
            return;
        }

        $body = '<p>Dear [name],</p>'
            .'<p>The National Executive of the Catholic Women\'s Association (CWA) Cameroon is pleased to admit you as a member. Your <strong>year of joining</strong> is <strong>[year]</strong>.</p>'
            .'<p>Diocese: [diocese]<br>Parish: [parish]</p>'
            .'[portrait]'
            .'<p>We welcome you into this apostolate of faith, service and sisterhood. May Our Lady of the Immaculate Conception accompany you.</p>'
            .'<p><em>To serve and not to be served</em> (Mt 20:28)</p>';

        DB::table('letter_templates')->insert([
            'category_id' => $categoryId,
            'name' => self::TEMPLATE_NAME,
            'header' => 'Catholic Women\'s Association (CWA) Cameroon',
            'subject' => 'Letter of Admission — [name]',
            'body' => $body,
            'footer' => 'To serve and not to be served (Mt 20:28)',
            'is_active' => 1,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        if (! Schema::hasTable('letter_templates')) {
            return;
        }
        DB::table('letter_templates')->where('name', self::TEMPLATE_NAME)->delete();
    }
}
