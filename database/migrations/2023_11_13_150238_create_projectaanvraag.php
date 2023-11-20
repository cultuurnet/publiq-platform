<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('projectaanvraag', function (Blueprint $table) {
            $table->uuid('integration_id')->index()->unique();
            $table->integer('projectaanvraag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projectaanvraag');
    }
};
