<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->timestamp('reminder_email_sent')->after('migrated_at')->nullable();
            $table->index('reminder_email_sent', 'reminder_email_sent_index');
        });
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('reminder_email_sent');
        });
    }
};
