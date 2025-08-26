<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_segment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_segment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contact_segment_id', 'contact_id']);
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_segment_members');
    }
};
