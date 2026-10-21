<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->string('email_domain')->nullable();
            $table->string('phone')->nullable();
            $table->string('ruc')->nullable();
            $table->string('department');
            $table->string('city')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('code')->unique();
            $table->string('discount_type');
            $table->unsignedInteger('discount_value');
            $table->date('starts_at')->nullable();
            $table->date('expires_at');
            $table->unsignedInteger('max_redemptions')->default(100);
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->json('channels')->nullable();
            $table->json('payment_methods')->nullable();
            $table->text('terms')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('in_store');
            $table->string('notes')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('categories');
    }
};
