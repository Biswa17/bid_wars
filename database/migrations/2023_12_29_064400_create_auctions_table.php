<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id'); 
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('starting_price', 10, 2);
            $table->decimal('maximum_price', 10, 2);
            $table->string('status')->default('review');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        DB::statement('ALTER TABLE auctions ADD CONSTRAINT chk_start_end_times CHECK (end_time > start_time)');
        DB::statement('ALTER TABLE auctions ADD CONSTRAINT chk_start_max_prices CHECK (starting_price < maximum_price)');
    }

    public function down()
    {
        Schema::dropIfExists('auctions');
    }
}
