<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_request_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('rater_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('score', [1, 2, 3, 4, 5]);
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['maintenance_request_id', 'rater_id']);

            $table->index('rater_id');
            $table->index('maintenance_request_id');
            $table->index(['technician_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_ratings');
    }
};
