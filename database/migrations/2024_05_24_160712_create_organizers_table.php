<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organizers', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->index();
            $table->uuid('organizer_id')->index();
            $table->timestamps();

            $table->unique(['integration_id', 'organizer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizers');
    }
};
