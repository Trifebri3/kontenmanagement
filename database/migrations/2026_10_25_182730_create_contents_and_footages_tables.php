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
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('title');
            $table->string('channel')->default('tiktok');
            $table->string('status')->default('shooting'); // idea, shooting, editing, review, ready, published
            $table->text('hook')->nullable();
            $table->text('concept')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'status']);
        });

        Schema::create('content_footages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('content_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('visual_notes')->nullable();
            $table->string('shot_type')->default('medium'); // wide, medium, close_up, macro, drone, pov
            $table->boolean('is_captured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('content_todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('content_items')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_todos');
        Schema::dropIfExists('content_footages');
        Schema::dropIfExists('content_items');
    }
};
