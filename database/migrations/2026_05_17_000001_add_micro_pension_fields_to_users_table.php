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
            $table->string('occupation')->nullable()->after('phone');
            $table->string('location')->nullable()->after('occupation');
            $table->string('role')->default('worker')->after('location');
            $table->string('api_token', 100)->nullable()->unique()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'occupation', 'location', 'role', 'api_token']);
        });
    }
};
