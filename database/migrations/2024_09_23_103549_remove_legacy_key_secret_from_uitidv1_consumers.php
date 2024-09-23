<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('uitidv1_consumers', function (Blueprint $table) {
            $table->dropColumn('consumer_key');
            $table->dropColumn('consumer_secret');
        });
    }

    public function down(): void
    {
        Schema::table('uitidv1_consumers', function (Blueprint $table) {
            $table->string('consumer_secret')->after('consumer_id')->index();
            $table->string('consumer_key')->after('consumer_id')->index();
        });
    }
};
