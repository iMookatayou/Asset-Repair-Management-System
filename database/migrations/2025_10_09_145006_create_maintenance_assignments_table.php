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
        Schema::create('maintenance_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_request_id')
                ->constrained('maintenance_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('role', 50)->nullable();
            $table->boolean('is_lead')->default(false);

            $table->dateTime('assigned_at')->nullable();

            $table->enum('status', ['in_progress', 'done', 'cancelled'])->default('in_progress');

            $table->timestamps();

            $table->unique(['maintenance_request_id', 'user_id'], 'uniq_request_user');

            $table->index(['user_id', 'status']);
            $table->index(['maintenance_request_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_assignments');
    }
};
