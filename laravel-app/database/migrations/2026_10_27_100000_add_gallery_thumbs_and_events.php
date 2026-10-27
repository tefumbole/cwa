<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGalleryThumbsAndEvents extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('gallery_items', 'thumb_path')) {
            Schema::table('gallery_items', function (Blueprint $table) {
                $table->string('thumb_path')->nullable()->after('file_path');
            });
        }
        if (! Schema::hasColumn('gallery_items', 'event_id')) {
            Schema::table('gallery_items', function (Blueprint $table) {
                $table->string('event_id', 64)->nullable()->after('description');
            });
        }
        if (! Schema::hasColumn('gallery_items', 'event_label')) {
            Schema::table('gallery_items', function (Blueprint $table) {
                $table->string('event_label', 120)->nullable()->after('event_id');
            });
        }
    }

    public function down()
    {
        $drop = array_values(array_filter(['event_label', 'event_id', 'thumb_path'], function ($column) {
            return Schema::hasColumn('gallery_items', $column);
        }));
        if ($drop) {
            Schema::table('gallery_items', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
}
