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
        Schema::table('users', function (Blueprint $table) {
            // Add created_by column (self-referencing foreign key)
            $table->unsignedBigInteger('created_by')->nullable()->after('remember_token');
            
            // Add soft deletes
            $table->softDeletes(); // This adds 'deleted_at' column
            
            // Add foreign key constraint for created_by
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Add index for better performance
            $table->index('created_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['created_by']);
            
            // Drop indexes
            $table->dropIndex(['created_by']);
            $table->dropIndex(['deleted_at']);
            
            // Drop columns
            $table->dropColumn('created_by');
            $table->dropColumn('deleted_at');
        });
    }
};