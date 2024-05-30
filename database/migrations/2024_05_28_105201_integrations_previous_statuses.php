<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('integrations_previous_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->unique();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations_previous_statuses');
    }
};
