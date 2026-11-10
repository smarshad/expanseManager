<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('bank_name', 120)->nullable();
            $table->string('account_type', 40);
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('payment_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'payment_method_id', 'name'], 'platform_user_method_name_unique');
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('account_payment_method', function (Blueprint $table) {
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->primary(['account_id', 'payment_method_id']);
        });

        Schema::create('account_payment_platform', function (Blueprint $table) {
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_platform_id')->constrained()->cascadeOnDelete();
            $table->primary(['account_id', 'payment_platform_id'], 'account_platform_primary');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('payment_method_id')->constrained()->nullOnDelete();
            $table->foreignId('payment_platform_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });

        if (Schema::hasColumn('expenses', 'bank_account')) {
            Schema::table('expenses', fn (Blueprint $table) => $table->dropColumn('bank_account'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('expenses', 'bank_account')) {
            Schema::table('expenses', fn (Blueprint $table) => $table->string('bank_account')->nullable());
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_platform_id');
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::dropIfExists('account_payment_platform');
        Schema::dropIfExists('account_payment_method');
        Schema::dropIfExists('payment_platforms');
        Schema::dropIfExists('accounts');
    }
};
