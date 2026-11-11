<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->id('Block_ID');
            $table->foreignId('Blocker_ID')->constrained('users', 'User_ID')->onDelete('cascade');
            $table->foreignId('Blocked_User_ID')->constrained('users', 'User_ID')->onDelete('cascade');
            $table->timestamp('Created_At')->useCurrent();
            $table->unique(['Blocker_ID', 'Blocked_User_ID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_users');
    }
};
