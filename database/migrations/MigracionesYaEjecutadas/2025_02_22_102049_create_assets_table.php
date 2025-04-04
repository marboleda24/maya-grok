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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('ticker')->unique();              // Ticker único del activo (ej. "AAPL")            
            $table->decimal('current_price', 15, 2);
            $table->decimal('lowest_price_bought', 15, 2)->nullable();
            $table->decimal('highest_price_reached', 15, 2)->nullable();
            $table->decimal('monitoring_point', 15, 2);
            $table->text('comments')->nullable();            // Comentarios sobre el activo            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
