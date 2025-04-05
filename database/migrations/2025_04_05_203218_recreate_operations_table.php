<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateOperationsTable extends Migration
{
    public function up()
    {
        // Eliminar la tabla existente
        Schema::dropIfExists('operations');

        // Crear la nueva tabla operations
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'buy' para compras
            $table->decimal('purchase_price', 15, 2)->nullable(); // Precio de compra
            $table->decimal('sale_price', 15, 2)->nullable(); // Precio de venta
            $table->decimal('quantity', 15, 2); // Cantidad operada
            $table->string('exchange'); // Exchange o broker
            $table->decimal('buy_commission', 15, 2)->nullable(); // Comisión de compra
            $table->decimal('sell_commission', 15, 2)->nullable(); // Comisión de venta
            $table->text('comments')->nullable(); // Comentarios
            $table->string('status')->default('open'); // 'open' o 'closed'
            $table->timestamp('closed_at')->nullable(); // Fecha de cierre (venta)
            $table->decimal('profitability', 15, 6)->nullable(); // Rentabilidad calculada
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        // Revertir: eliminar la tabla
        Schema::dropIfExists('operations');

        // Opcional: recrear la versión anterior si quieres rollback (ajústalo según tu esquema original)
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('quantity', 15, 2);
            $table->string('exchange');
            $table->decimal('buy_commission', 15, 2)->nullable();
            $table->decimal('sell_commission', 15, 2)->nullable();
            $table->text('comments')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('profitability', 15, 6)->nullable();
            $table->timestamps();
        });
    }
}