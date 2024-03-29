<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('insightly_mappings', static function (Blueprint $table) {
            $table->uuid('id')->index();
            $table->string('insightly_id')->index();
            $table->string('resource_type')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insightly_mapping');
    }
};
