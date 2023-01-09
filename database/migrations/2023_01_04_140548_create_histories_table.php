<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('user_id');
            $table->string('model');
            $table->string('event');
            $table->timestamp('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
