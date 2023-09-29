<?php

declare(strict_types=1);

use App\Domain\Integrations\IntegrationPartnerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('integrations', static function (Blueprint $table) {
            $table->string('partner_status')->default(IntegrationPartnerStatus::THIRD_PARTY->value)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrations', static function (Blueprint $table) {
            $table->dropColumn('partner_status');
        });
    }
};
