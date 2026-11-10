<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('categories', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('color_code');
            }
            if (!Schema::hasColumn('categories', 'budget_type')) {
                $table->enum('budget_type', ['no_budget', 'soft_budget', 'hard_budget', 'fixed'])->default('soft_budget')->after('sort_order');
            }
            if (!Schema::hasColumn('categories', 'monthly_budget')) {
                $table->decimal('monthly_budget', 12, 2)->nullable()->after('budget_type');
            }
            if (!Schema::hasColumn('categories', 'nature')) {
                $table->enum('nature', ['need', 'want', 'investment'])->default('need')->after('monthly_budget');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('bank_account');
            }
            if (!Schema::hasColumn('expenses', 'frequency')) {
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->nullable()->after('is_recurring');
            }
            if (!Schema::hasColumn('expenses', 'nature')) {
                $table->enum('nature', ['need', 'want', 'investment'])->nullable()->after('frequency');
            }
        });


        Schema::table('categories', function (Blueprint $table) {
            $table->index('budget_type');
            $table->index('nature');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['is_recurring', 'frequency']);
            $table->index('nature');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            foreach (['nature', 'frequency', 'is_recurring'] as $column) {
                if (Schema::hasColumn('expenses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            foreach (['nature', 'monthly_budget', 'budget_type', 'sort_order'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
