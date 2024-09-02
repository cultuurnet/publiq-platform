<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('keycloak_clients', function (Blueprint $table) {
            $table->index('integration_id', 'integration_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('keycloak_clients', function (Blueprint $table) {
            $table->dropIndex('integration_id_index');
        });
    }
};
