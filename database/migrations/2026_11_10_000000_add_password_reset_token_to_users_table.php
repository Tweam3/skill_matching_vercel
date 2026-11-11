<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('Password_Reset_Token', 64)->nullable()->unique()->after('Password_Hash');
            $table->timestamp('Password_Reset_Expires_At')->nullable()->after('Password_Reset_Token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['Password_Reset_Token', 'Password_Reset_Expires_At']);
        });
    }
};