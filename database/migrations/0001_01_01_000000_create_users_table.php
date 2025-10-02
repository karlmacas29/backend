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
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); // Replace email with username
            $table->string('position');
            $table->boolean('active')->default(true); // Add active column
            // $table->string('email')->nullable(); // <--- After 'active'
            $table->timestamp('email_verified_at')->nullable()->useCurrent()->setTimezone('Asia/Manila');
            $table->timestamp('created_at')->useCurrent()->setTimezone('Asia/Manila');
            $table->timestamp('updated_at')->useCurrent()->setTimezone('Asia/Manila');
            $table->string('password');
              $table->foreignId('role_id')// 1 is for admin, 2 is for rater
                ->nullable() // allow nulls for existing users
                ->after('active') // place it right after the ID
                ->constrained()
                ->onDelete('cascade');
            $table->rememberToken();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary(); // Change email to username
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
