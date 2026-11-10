<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CompraController extends Controller
{
    // R8: Exibe apenas as compras do usuário logado
    public function index()
    {
        // Busca as vendas do cliente logado
        $vendas = DB::table('vendas')
            ->where('cliente_id', Auth::id())
            ->get();

        foreach ($vendas as $venda) {
            // Converte a string de data em objeto Carbon para o método format() funcionar
            if (isset($venda->created_at)) {
                $venda->created_at = Carbon::parse($venda->created_at);
            }

            // Busca os itens comprados na tabela pivô
            $itensComprados = DB::table('produto_venda')
                ->join('produtos', 'produto_venda.produto_id', '=', 'produtos.id')
                ->where('produto_venda.venda_id', $venda->id)
                ->select('produtos.nome', 'produto_venda.quantidade', 'produto_venda.preco_unitario')
                ->get();

            // R7/R8: Monta a estrutura de sub-objetos fake que o arquivo .blade do professor espera ler
            $produtosModificados = collect();
            foreach ($itensComprados as $item) {
                $subObjeto = new \stdClass();
                $subObjeto->nome = $item->nome;
                
                // Cria o objeto em cascata fictício ->pivot->quantidade e ->pivot->preco_unitario
                $subObjeto->pivot = new \stdClass();
                $subObjeto->pivot->quantidade = $item->quantidade;
                $subObjeto->pivot->preco_unitario = $item->preco_unitario;

                $produtosModificados->push($subObjeto);
            }

            // Vincula a coleção moldada de volta à venda correspondente
            $venda->produtos = $produtosModificados;
        }

        return view('compras.index', compact('vendas'));
    }

    // Abre a tela do formulário de compras
    public function create()
    {
        $produtos = Produto::all();
        return view('compras.create', compact('produtos'));
    }

    // R7 + Bônus de Estoque: Processa a compra
    public function store(Request $request)
    {
        $itens = $request->input('itens',);

        // 1. Ignora itens com quantidade vazia ou igual a zero
        $itensFiltrados = array_filter($itens, function ($quantidade) {
            return $quantidade > 0;
        });

        if (empty($itensFiltrados)) {
            return back()->withErrors(['error' => 'Selecione ao menos um produto com quantidade maior que zero.']);
        }

        // Usa uma transação para garantir que tudo salve perfeitamente ou nada seja alterado
        return DB::transaction(function () use ($itensFiltrados) {
            
            // BÔNUS: Valida se há estoque suficiente antes de fechar a compra
            foreach ($itensFiltrados as $produtoId => $quantidade) {
                $produto = Produto::find($produtoId);
                
                if (!$produto || $produto->estoque < $quantidade) {
                    return back()->withErrors([
                        'error' => "Estoque insuficiente para o produto: {$produto->nome} (Disponível: {$produto->estoque})."
                    ]);
                }
            }

            // 2. Cria o registro da venda direto na tabela do banco
            $vendaId = DB::table('vendas')->insertGetId([
                'cliente_id' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 3. Associa os produtos na tabela pivô e atualiza o estoque
            foreach ($itensFiltrados as $produtoId => $quantidade) {
                $produto = Produto::find($produtoId);

                // Grava o relacionamento com o preço atual do produto
                DB::table('produto_venda')->insert([
                    'venda_id' => $vendaId,
                    'produto_id' => $produtoId,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $produto->preco,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // BÔNUS: Diminui a quantidade comprada do estoque do produto
                $produto->decrement('estoque', $quantidade);
            }

            return redirect()->route('compras.index')->with('success', 'Compra realizada!');
        });
    }
}
