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
        Schema::create('computer_infos', function (Blueprint $table) {
            $table->id();
            $table->string('RAM');
            $table->string('processor');
            $table->string('GPU');
            $table->string('monitor');
            $table->string('headphones');
            $table->string('mouse');
            $table->string('keyboard');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computer_infos');
    }
};
