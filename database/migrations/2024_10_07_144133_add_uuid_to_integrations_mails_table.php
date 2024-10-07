<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('integrations_mails', function (Blueprint $table) {
            // Add the UUID field (but don't set it as primary key yet)
            $table->uuid('id')->nullable()->first();
        });

        // Autofill UUIDs for existing records to prevent null errors later
        DB::table('integrations_mails')->update(['id' => Uuid::uuid4()->toString()]);

        // Finish the migration, drop existing primary key and make the UUID field non-nullable and set it as the primary key
        Schema::table('integrations_mails', function (Blueprint $table) {
            $table->dropPrimary(['integration_id', 'template_name']);
            $table->uuid('id')->primary()->change();

            $table->index(['integration_id', 'template_name'], 'integration_id_template_name_index');
        });
    }

    public function down(): void
    {
        Schema::table('integrations_mails', function (Blueprint $table) {
            $table->dropPrimary(['id']);

            $table->dropIndex('integration_id_template_name_index');

            $table->primary(['integration_id', 'template_name']);

            $table->dropColumn('id');
        });
    }
};
