<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthRefreshTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.oauth.tables.refresh_tokens'), function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100);
            $table->boolean('is_revoked');
            $table->dateTime('expires_at')->nullable();

            // Indexes
            $table->index(['access_token_id']);
            $table->foreign('access_token_id')->references('id')->on(config('rinvex.oauth.tables.access_tokens'))
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
        Schema::dropIfExists(config('rinvex.oauth.tables.refresh_tokens'));
    }
}
