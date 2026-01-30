<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pays', function (Blueprint $table) {
            $table->decimal('fee_rate', 5, 2)->default(0)->after('is_open')->comment('费率百分比');
            $table->decimal('fee_fixed', 10, 2)->default(0)->after('fee_rate')->comment('固定手续费');
        });
    }

    public function down(): void
    {
        Schema::table('pays', function (Blueprint $table) {
            $table->dropColumn(['fee_rate', 'fee_fixed']);
        });
    }
};
