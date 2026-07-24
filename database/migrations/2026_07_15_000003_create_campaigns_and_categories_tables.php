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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 50)->default('draft');
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('date_label', 100)->nullable();
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->string('year', 10)->nullable();
            $table->string('supporters_label', 100)->nullable();
            $table->string('signatures_label', 100)->nullable();
            $table->string('cta_text', 100)->nullable()->default('Read More');
            $table->string('link')->nullable();
            $table->string('signup_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'deleted_at']);
        });

        Schema::create('campaign_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('campaign_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('campaign_categories')->onDelete('cascade');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('featured_order')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'category_id']);
            $table->index(['category_id', 'order'], 'assignments_category_order_index');
            $table->index(['category_id', 'featured', 'featured_order'], 'assignments_category_featured_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_category_assignments');
        Schema::dropIfExists('campaign_categories');
        Schema::dropIfExists('campaigns');
    }
};
