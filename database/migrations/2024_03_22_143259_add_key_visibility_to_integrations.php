<?php

declare(strict_types=1);

use App\Domain\Integrations\KeyVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->string('key_visibility')->default(KeyVisibility::v2);
        });
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('key_visibility');
        });
    }
};
