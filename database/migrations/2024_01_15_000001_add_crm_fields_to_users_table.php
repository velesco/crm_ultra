<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('timezone')->default('UTC')->after('avatar');
            $table->string('language')->default('en')->after('timezone');
            $table->boolean('is_active')->default(true)->after('language');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->json('settings')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'timezone', 'language', 
                'is_active', 'last_login_at', 'settings'
            ]);
        });
    }
};
