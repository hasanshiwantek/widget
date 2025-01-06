<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewer')->nullable();
            $table->string('location')->nullable();
            $table->integer('totalReviews')->nullable();
            $table->string('date')->nullable();
            $table->string('reviewHeading')->nullable();
            $table->text('reviewContent')->nullable();
            $table->string('dateOfExperience')->nullable();
            $table->string('stars')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
