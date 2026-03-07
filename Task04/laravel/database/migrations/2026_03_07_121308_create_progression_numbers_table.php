<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progression_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progression_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->integer('number');
            $table->boolean('is_missing')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progression_numbers');
    }
};