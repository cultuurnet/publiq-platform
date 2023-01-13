<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('coupons', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id')->nullable();
            $table->string('code')->unique();
            $table->boolean('is_used')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
