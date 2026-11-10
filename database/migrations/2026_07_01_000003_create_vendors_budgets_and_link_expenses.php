<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->date('month');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'month']);
            $table->index(['user_id', 'month']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('vendor_id')
                ->nullable()
                ->after('payment_method_id')
                ->constrained('vendors')
                ->nullOnDelete();

            $table->index(
                ['created_by', 'expense_date', 'vendor_id'],
                'expenses_user_date_vendor_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_user_date_vendor_index');
            $table->dropConstrainedForeignId('vendor_id');
        });

        Schema::dropIfExists('budgets');
        Schema::dropIfExists('vendors');
    }
};
