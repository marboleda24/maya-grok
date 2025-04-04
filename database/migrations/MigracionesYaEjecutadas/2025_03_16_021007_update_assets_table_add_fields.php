<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssetsTableAddFields extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('symbol')->after('name')->nullable(); // Añadimos symbol
            $table->text('comments')->nullable()->after('value');
            $table->decimal('max_value', 15, 2)->nullable()->after('comments');
            $table->decimal('base_value', 15, 2)->nullable()->after('max_value');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['symbol', 'comments', 'max_value', 'base_value']);
        });
    }
}
