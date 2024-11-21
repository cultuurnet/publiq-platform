<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('integrations_on_hold', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->unique();
            $table->boolean('on_hold')->nullable();
            $table->string('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('integrations_on_hold');
    }
};
