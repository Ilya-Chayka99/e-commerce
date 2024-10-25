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
        Schema::create('perms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('edit_tarif');
            $table->boolean('edit_computer');
            $table->boolean('add_perms');
            $table->boolean('free_rentals');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perms');
    }
};
