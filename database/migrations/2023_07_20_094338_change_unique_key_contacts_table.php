<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contacts', static function (Blueprint $table) {
            $table->dropUnique(['integration_id', 'email', 'type']);
            $table->boolean('deleted')->virtualAs("IF(`deleted_at` IS NULL, false, true)");
            $table->unique(['integration_id', 'email', 'type', 'deleted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', static function (Blueprint $table) {
            $table->dropUnique(['integration_id', 'email', 'type', 'deleted']);
            $table->dropColumn('deleted');
            $table->unique(['integration_id', 'email', 'type']);
        });
    }
};
