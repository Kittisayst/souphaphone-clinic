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
       Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('queue_number', 10)->comment('A001, A002 ຫຼື 001, 002');
            $table->date('queue_date');
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->enum('status', ['waiting', 'called', 'in_progress', 'completed', 'cancelled'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Indexes & Constraints
            $table->unique(['queue_number', 'queue_date']);
            $table->index(['status', 'queue_date']);
            $table->index(['patient_id', 'queue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
