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
        Schema::table('contacts', function (Blueprint $table) {
            // First, modify the existing source column to be enum
            $table->dropColumn('source');
        });
        
        Schema::table('contacts', function (Blueprint $table) {
            // Add the enum source column
            $table->enum('source', ['manual', 'gmail', 'sheets', 'api', 'import'])
                  ->default('manual')
                  ->after('status');
            
            // Add metadata for enrichment from different sources
            $table->json('source_metadata')->nullable()->after('source');
            
            // Track Gmail-specific data
            $table->timestamp('first_email_at')->nullable()->after('source_metadata');
            $table->timestamp('last_email_at')->nullable()->after('first_email_at');
            $table->integer('email_count')->default(0)->after('last_email_at');
            
            // Enhanced contact information from email signatures
            $table->string('website')->nullable()->after('country');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('twitter_handle')->nullable()->after('linkedin_url');
            $table->json('social_profiles')->nullable()->after('twitter_handle');
            
            // Team scoping (add simple team_id without foreign key for now)
            if (!Schema::hasColumn('contacts', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('assigned_to');
                $table->index('team_id'); // Add index but no foreign key constraint for now
            }
            
            // Improve indexing for Gmail integration
            $table->index(['source', 'created_at']);
            $table->index(['email', 'team_id']); // For team-scoped deduplication
            $table->index(['last_email_at', 'email_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'source_metadata',
                'first_email_at',
                'last_email_at', 
                'email_count',
                'linkedin_url',
                'twitter_handle',
                'social_profiles'
            ]);
            
            // Only drop team_id if we added it
            if (Schema::hasColumn('contacts', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }
        });
    }
};
