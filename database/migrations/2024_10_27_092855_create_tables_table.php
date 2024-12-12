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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('metadata_id')->unsigned();
            $table->bigInteger('info_id')->unsigned();
            $table->bigInteger('matrix_id')->unsigned();
            $table->timestamps();

            $table->foreign('metadata_id')->references('id')->on('table_metadata')->onDelete('cascade');
            $table->foreign('info_id')->references('id')->on('table_infos')->onDelete('cascade');
            $table->foreign('matrix_id')->references('id')->on('matrix_halls')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
