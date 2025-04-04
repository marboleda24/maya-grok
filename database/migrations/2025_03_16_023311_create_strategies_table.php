<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStrategiesTable extends Migration
{
    public function up()
    {
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->decimal('buy_threshold', 15, 2); // Umbral para comprar
            $table->decimal('sell_threshold', 15, 2); // Umbral para vender
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('strategies');
    }
}