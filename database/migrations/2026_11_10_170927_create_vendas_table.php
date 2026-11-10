<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('produto_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->integer('quantidade');
            $table->decimal('preco_unitario', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_venda');
        Schema::dropIfExists('vendas');
    }
};
