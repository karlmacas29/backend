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
        Schema::table('nPersonalInfo', function (Blueprint $table) {
            Schema::table('nPersonalInfo', function (Blueprint $table) {
                $table->boolean('is_temporary')->default(false)->after('id');
                $table->timestamp('temp_expires_at')->nullable()->after('is_temporary');

                // Add index for faster cleanup queries
                $table->index(['is_temporary', 'temp_expires_at']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nPersonalInfo', function (Blueprint $table) {
            $table->dropIndex(['is_temporary', 'temp_expires_at']);
            $table->dropColumn(['is_temporary', 'temp_expires_at']);
        });
    }
};
