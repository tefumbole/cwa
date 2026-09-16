<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCwaMembersTable extends Migration
{
    public function up()
    {
        Schema::create('cwa_members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone', 30)->index();
            $table->string('diocese');
            $table->string('parish');
            $table->string('email')->nullable();
            $table->string('age_range', 40)->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('portrait_path')->nullable();
            $table->string('id_front_path')->nullable();
            $table->string('id_back_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamp('bylaws_agreed_at')->nullable();
            $table->string('status', 32)->default('awaiting_approval')->index();
            $table->timestamp('admitted_at')->nullable();
            $table->unsignedSmallInteger('admitted_year')->nullable();
            $table->unsignedInteger('letter_id')->nullable()->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cwa_members');
    }
}
