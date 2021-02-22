<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthAuthCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.oauth.tables.auth_codes'), function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->integer('user_id')->unsigned();
            $table->string('user_type');
            $table->integer('client_id')->unsigned();
            $table->boolean('is_revoked')->default(false);
            $table->dateTime('expires_at')->nullable();

            // Indexes
            $table->index(['user_type', 'user_id'], 'auth_codes_user_type_id');
            $table->foreign('client_id')->references('id')->on(config('rinvex.oauth.tables.clients'))
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.oauth.tables.auth_codes'));
    }
}
