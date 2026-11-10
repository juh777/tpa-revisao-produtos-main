<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CompraController;

// ==========================================
// Rotas Públicas (Qualquer pessoa acessa)
// ==========================================
Route::get('/', function () {
    return redirect()->route('produtos.index');
});

// Mantém o nome padrão exigido pelas telas
Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');

// Criada a rota 'home' separada para o botão do menu
Route::get('/home', function () {
    return redirect()->route('produtos.index');
})->name('home');

// Criada a rota 'sobre' separada para corrigir o erro do menu do template
Route::get('/sobre', function () {
    return redirect()->route('produtos.index');
})->name('sobre');

// Rotas de Autenticação (Gerenciadas pelo LoginController)
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// R3: Rotas Protegidas (Só usuário logado)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // R4: Cadastro de Produtos Protegido (Só quem for do tipo 'vendedor' logado acessa)
    Route::middleware(['vendedor'])->group(function () {
        Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
        Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
    });

    // Rotas de Compras (Qualquer tipo de usuário logado acessa)
    Route::get('/compras/create', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
});

