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
        Schema::table('location_users', function (Blueprint $table) {
            $table->uuid('user_uuid')->nullable()->after('user_id');
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->uuid('user_uuid')->nullable()->after('user_id');
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_users', function (Blueprint $table) {
            $table->dropColumn('user_uuid');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('user_uuid');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
