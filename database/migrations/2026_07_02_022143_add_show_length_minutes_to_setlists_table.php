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
        Schema::table('setlists', function (Blueprint $table) {
            $table->unsignedSmallInteger('show_length_minutes')->nullable()->after('number_of_sets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setlists', function (Blueprint $table) {
            $table->dropColumn('show_length_minutes');
        });
    }
};
