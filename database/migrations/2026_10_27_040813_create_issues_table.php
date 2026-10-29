<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();

            // Public Identifier
            $table->uuid('uuid')->unique();

            // Issue ownership
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // Assigned staff member
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Related records
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();

            $table->foreignId('issue_category_id')->constrained('issue_categories')->restrictOnDelete();

            // Issue details
            $table->string('title');
            $table->string('description');

            // Workflow
            $table->string('status')->default('pending')->index();

            $table->string('priority')->default('medium')->index();

            // optimal map coordinates
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Resolution tracking
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // common filtering and sorting
            $table->index(['user_id', 'created_at']);
            $table->index(['barangay_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['issue_category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
