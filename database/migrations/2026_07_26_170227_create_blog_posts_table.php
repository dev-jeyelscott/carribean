<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the lightweight restaurant Blog CMS table.
     */
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();

            $table->string('title', 180);
            $table->string('slug', 180)->unique();

            $table->text('excerpt')->nullable();
            $table->longText('body');

            $table->string('image_path')->nullable();
            $table->string('image_alt_text', 255)->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 255)->nullable();

            $table->timestamps();

            $table->index([
                'is_published',
                'published_at',
            ]);
        });
    }

    /**
     * Remove the Blog CMS table during a deliberate rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
