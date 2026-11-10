<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description', 500);
            $table->decimal('amount', 10, 2);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('expense_type_id')->constrained('expense_types');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('file_path', 500)->nullable();
            $table->text('cashback')->nullable();
            $table->text('notes')->nullable();
            $table->date('expense_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('category_id');
            $table->index('expense_type_id');
            $table->index('payment_method_id');
            $table->index('created_by');
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
