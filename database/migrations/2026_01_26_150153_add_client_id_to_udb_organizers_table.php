<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('udb_organizers', static function (Blueprint $table) {
            $table->uuid('client_id')->nullable()->after('organizer_id');
            $table->foreign('client_id')->references('id')->on('keycloak_clients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('udb_organizers', static function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
