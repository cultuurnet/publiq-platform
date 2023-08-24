<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
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

        // Migrate old clients that don't have an id as a primary key
        DB::statement('UPDATE auth0_clients SET id = UUID() WHERE id \'\'');
    }

    public function down(): void
    {
        Schema::dropIfExists('auth0_clients');
    }
};
