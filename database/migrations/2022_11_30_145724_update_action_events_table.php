<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {

    public function up(): void
    {
        Schema::table('action_events', function (Blueprint $table) {
            $table->string('user_id', 42)->change();
        });
    }

    public function down(): void
    {
        Schema::table('action_events', function (Blueprint $table) {
            $table->string('user_id', 36)->change();
        });
    }
};
