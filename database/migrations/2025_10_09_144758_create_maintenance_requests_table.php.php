<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                ->constrained('assets')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('priority', ['low','medium','high','urgent'])->default('medium');
            $table->enum('status', ['pending','in_progress','completed','cancelled'])->default('pending');

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamp('request_date')->useCurrent();
            $table->timestamp('assigned_date')->nullable();
            $table->timestamp('completed_date')->nullable();

            $table->text('remark')->nullable();

            $table->decimal('cost', 10, 2)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['asset_id', 'request_date']);
            $table->index(['status', 'priority']);
            $table->index(['technician_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('maintenance_requests');
    }
};
