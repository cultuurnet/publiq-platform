<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('auth0_clients', function (Blueprint $table) {
            $table->dropColumn('distributed_at');
        });

        Schema::table('uitidv1_consumers', function (Blueprint $table) {
            $table->dropColumn('distributed_at');
        });
    }

    public function down(): void
    {
        Schema::table('auth0_clients', function (Blueprint $table) {
            $table->timestamp('distributed_at')->after('deleted_at')->nullable();
        });

        Schema::table('uitidv1_consumers', function (Blueprint $table) {
            $table->timestamp('distributed_at')->after('deleted_at')->nullable();
        });
    }
};
