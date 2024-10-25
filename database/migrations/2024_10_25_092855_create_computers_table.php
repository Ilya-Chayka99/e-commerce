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
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('metadata_id')->unsigned();
            $table->bigInteger('info_id')->unsigned();
            $table->timestamps();

            $table->foreign('metadata_id')->references('id')->on('computer_metadatas')->onDelete('cascade');
            $table->foreign('info_id')->references('id')->on('computer_infos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
