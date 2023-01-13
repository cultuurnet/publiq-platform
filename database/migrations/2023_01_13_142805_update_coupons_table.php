<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('coupons', static function (Blueprint $table) {
            $table->boolean('distributed')->after('id')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('coupons', static function (Blueprint $table) {
            $table->dropColumn('distributed');
        });
    }
};
