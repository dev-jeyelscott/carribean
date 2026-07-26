<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the flat, sortable FAQ table.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();

            $table->string('question', 255);
            $table->longText('answer');

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);

            $table->timestamps();

            $table->index([
                'is_visible',
                'sort_order',
            ]);
        });
    }

    /**
     * Remove the FAQ table during a deliberate rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
