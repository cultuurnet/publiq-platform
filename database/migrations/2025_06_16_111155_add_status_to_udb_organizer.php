<?php

declare(strict_types=1);

use App\Domain\Integrations\UdbOrganizerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('udb_organizers', static function (Blueprint $table) {
            $table->string('status', 10)->default(UdbOrganizerStatus::Pending);
        });
    }

    public function down(): void
    {
        Schema::table('udb_organizers', static function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
