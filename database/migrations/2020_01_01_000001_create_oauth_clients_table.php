<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.oauth.tables.clients'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('user_type');
            $table->json('name');
            $table->string('grant_type', 100);
            $table->string('secret', 100)->nullable();
            $table->text('redirect');
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['user_type', 'user_id'], 'clients_user_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
}
