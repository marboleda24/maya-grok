<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStrategyIdToAssetsTable extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('strategy_id')->nullable()->after('portfolio_id')->constrained('strategies')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['strategy_id']);
            $table->dropColumn('strategy_id');
        });
    }
}