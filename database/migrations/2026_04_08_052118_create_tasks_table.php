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
        Schema::create('TASKS', function (Blueprint $table) {
            $table->id('nTaskNo');
            $table->string('cTaskName');
            $table->text('cTasksDescription')->nullable();
            $table->string('cTaskPriority', 64)->default('Normal');
            $table->boolean('cCompleted')->default(false);
            $table->timestamps();
            $table->foreignId('nTaskListNo')->references('nTaskListNo')->on('TASK_LISTS')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TASKS');
    }
};
