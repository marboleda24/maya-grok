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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('quantity', 15, 2);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('status')->default('open'); // 'open' o 'closed'
            $table->timestamp('closed_at')->nullable();
            $table->string('exchange');                         // Exchange o broker (ej. "Binance")
            $table->text('comments')->nullable();               // Comentarios sobre la operación
            $table->decimal('buy_commission', 15, 2)->default(0.00);  // Comisión de compra
            $table->decimal('sell_commission', 15, 2)->nullable();    // Comisión de venta                        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
