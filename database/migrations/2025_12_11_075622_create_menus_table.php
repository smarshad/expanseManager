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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('route')->nullable(); // Laravel route name
            $table->string('url')->nullable(); // External URL or fallback
            $table->string('icon', 100)->nullable(); // Font Awesome class or similar
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->foreignId('permission_id')->nullable()->constrained('permissions'); // For RBAC integration
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('parent_id');
            $table->index('permission_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
