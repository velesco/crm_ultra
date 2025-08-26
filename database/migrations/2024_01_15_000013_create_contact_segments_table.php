<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions')->nullable();
            $table->boolean('is_dynamic')->default(false);
            $table->string('color')->default('#3B82F6');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['is_dynamic', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_segments');
    }
};
