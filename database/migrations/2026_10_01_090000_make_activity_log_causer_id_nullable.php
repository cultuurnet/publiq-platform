<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * 2023_01_09_134468_update_activity_log widened causer_id to 255 characters, but a change()
 * restates the whole column definition, so leaving out nullable() silently turned the column
 * NOT NULL. Spatie omits causer_id entirely when there is no causer, so under MySQL strict mode
 * every model write outside an authenticated request failed with SQLSTATE[HY000] 1364.
 * */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('causer_id', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('causer_id', 255)->nullable(false)->change();
        });
    }
};
