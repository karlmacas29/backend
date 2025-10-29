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
        // Schema::dropIfExists('user_rsp_control');
        Schema::create('user_rsp_control', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->boolean('viewDashboardstat')->default(false);

            $table->boolean('viewPlantillaAccess')->default(false);
            $table->boolean('modifyPlantillaAccess')->default(false);

            $table->boolean('viewJobpostAccess')->default(false);
            $table->boolean('modifyJobpostAccess')->default(false);

            $table->boolean('viewActivityLogs')->default(false);
            $table->boolean('userManagement')->default(false);

            $table->boolean('viewRater')->default(false);
            $table->boolean('modifyRater')->default(false);

            $table->boolean('viewCriteria')->default(false);
            $table->boolean('modifyCriteria')->default(false);
            $table->boolean('viewReport')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rsp_control');
    }
};
