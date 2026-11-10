<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expense_types') && ! Schema::hasTable('expense_fors')) {
            Schema::rename('expense_types', 'expense_fors');
        }

        Schema::table('expense_fors', function (Blueprint $table) {
            if (! Schema::hasColumn('expense_fors', 'color_code')) {
                $table->string('color_code', 20)->nullable()->after('name');
            }

            if (! Schema::hasColumn('expense_fors', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('color_code');
            }

            if (! Schema::hasColumn('expense_fors', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'expense_type_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->renameColumn('expense_type_id', 'expense_for_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'expense_for_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->renameColumn('expense_for_id', 'expense_type_id');
            });
        }

        if (Schema::hasTable('expense_fors') && ! Schema::hasTable('expense_types')) {
            Schema::rename('expense_fors', 'expense_types');
        }
    }
};