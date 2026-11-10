<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('transaction_kind', 20)
                ->default('expense')
                ->after('amount');
            $table->string('spending_nature', 20)
                ->default('unclassified')
                ->after('transaction_kind');
            $table->string('recurrence', 20)
                ->default('none')
                ->after('spending_nature');

            $table->index(
                ['created_by', 'expense_date', 'transaction_kind'],
                'expenses_user_date_kind_index'
            );
            $table->index(
                ['created_by', 'expense_date', 'spending_nature'],
                'expenses_user_date_nature_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_user_date_kind_index');
            $table->dropIndex('expenses_user_date_nature_index');
            $table->dropColumn([
                'transaction_kind',
                'spending_nature',
                'recurrence',
            ]);
        });
    }
};
