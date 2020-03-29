<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickBooksTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quickbooks_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
            $table->unsignedBigInteger('realm_id');
            $table->longtext('access_token');
            $table->datetime('access_token_expires_at');
            $table->string('refresh_token');
            $table->datetime('refresh_token_expires_at');

            $table->timestamps();
// todo deal with this?
//            $table->foreign('user_id')
//                  ->references('id')
//                  ->on('users')
//                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quickbooks_token');
    }
}
