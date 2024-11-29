<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('admin_information', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->index('integration_id_index');
            $table->boolean('on_hold')->default(false);
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_information');
    }
};
