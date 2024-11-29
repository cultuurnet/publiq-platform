<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('auth0_clients');
    }

    public function down(): void
    {
        Schema::create('auth0_clients', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->index();
            $table->string('auth0_client_id')->index();
            $table->string('auth0_client_secret');
            $table->string('auth0_tenant')->index();
            $table->unique(['integration_id', 'auth0_tenant']);
            $table->unique(['auth0_client_id', 'auth0_tenant']);
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
