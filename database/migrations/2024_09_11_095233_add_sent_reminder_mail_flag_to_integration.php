<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->timestamp('sent_reminder_email')->after('migrated_at')->nullable();
            $table->index('sent_reminder_email', 'sent_reminder_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('sent_reminder_email');
        });
    }
};
