<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('contacts', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id');
            $table->string('email');
            $table->string('type');
            $table->string('first_name');
            $table->string('last_name');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['integration_id','email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
