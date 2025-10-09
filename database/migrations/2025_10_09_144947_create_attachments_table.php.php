<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('maintenance_requests')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->index(['request_id']);
            $table->index(['file_type']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('attachments');
    }
};
