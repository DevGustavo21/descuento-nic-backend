<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->string('customer_phone', 30)->nullable()->after('notes');
            $table->unique(['coupon_id', 'customer_phone'], 'redemptions_coupon_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropUnique('redemptions_coupon_customer_unique');
            $table->dropColumn('customer_phone');
        });
    }
};
