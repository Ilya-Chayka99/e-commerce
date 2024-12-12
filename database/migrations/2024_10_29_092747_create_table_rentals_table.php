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
        Schema::create('table_rentals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('table_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('rent_time');
            $table->bigInteger('minutes');
            $table->decimal('end_price', 8, 2);
            $table->timestamps();

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_rentals');
    }
};
