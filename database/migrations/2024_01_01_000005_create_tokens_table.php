<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_number');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('counter_id')->nullable();
            $table->unsignedBigInteger('issued_by');
            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'cancelled'])->default('waiting');
            $table->boolean('is_vip')->default(false);
            $table->timestamp('issued_at');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('set null');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
