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
            $table->string('identifier', 100);
            $table->string('access_token_identifier', 100);
            $table->boolean('is_revoked')->default(false);
            $table->dateTime('expires_at')->nullable();

            // Indexes
            $table->unique('identifier');
            $table->index(['access_token_identifier']);
            $table->foreign('access_token_identifier')->references('identifier')->on(config('rinvex.oauth.tables.access_tokens'))
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
