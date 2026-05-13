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
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->longText('lyrics')->nullable();
            $table->text('notes')->nullable();
            $table->string('tempo')->nullable();
            $table->string('key')->nullable();
            $table->boolean('has_track')->default(false);
            $table->boolean('is_acoustic')->default(false);
            $table->unsignedInteger('runtime')->nullable();
            $table->text('arrangement')->nullable();
            $table->timestamps();

            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
