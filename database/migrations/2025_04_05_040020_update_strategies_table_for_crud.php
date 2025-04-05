<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStrategiesTableForCrud extends Migration
{
    public function up()
    {
        Schema::table('strategies', function (Blueprint $table) {
            // Hacer asset_id nullable para que las estrategias sean independientes
            $table->foreignId('asset_id')->nullable()->change();

            // Añadir columnas en el orden correcto
            if (!Schema::hasColumn('strategies', 'name')) {
                $table->string('name')->after('asset_id');
            }
            if (!Schema::hasColumn('strategies', 'techo_threshold')) {
                $table->decimal('techo_threshold', 15, 2)->default(0.10)->after('sell_threshold');
            }
            if (!Schema::hasColumn('strategies', 'minimum_open_operations')) {
                $table->integer('minimum_open_operations')->default(2)->after('techo_threshold');
            }
            if (!Schema::hasColumn('strategies', 'comments')) {
                $table->text('comments')->nullable()->after('minimum_open_operations');
            }

            // Ajustar buy_threshold y sell_threshold
            $table->decimal('buy_threshold', 15, 2)->default(0.05)->change();
            $table->decimal('sell_threshold', 15, 2)->default(1.00)->change();
        });
    }

    public function down()
    {
        Schema::table('strategies', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable(false)->change();
            $table->dropColumn(['name', 'techo_threshold', 'minimum_open_operations', 'comments']);
            $table->float('buy_threshold')->nullable()->change();
            $table->float('sell_threshold')->nullable()->change();
        });
    }
}