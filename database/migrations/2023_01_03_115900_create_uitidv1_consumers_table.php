<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('uitidv1_consumers', static function (Blueprint $table) {
            $table->uuid('integration_id')->index();
            $table->string('consumer_key')->index();
            $table->string('consumer_secret');
            $table->string('api_key');
            $table->string('environment')->index();
            $table->unique(['integration_id', 'consumer_key']);
            $table->unique(['consumer_key', 'environment']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uitidv1_consumers');
    }
};
