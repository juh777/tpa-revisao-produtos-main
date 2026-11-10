<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Http\Requests\StoreProdutoRequest;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{
    public function index()
    {
        
        $produtos = Produto::with('vendedor')->get();

      
        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    public function store(StoreProdutoRequest $request)
    {
        $dados = $request->validated();
        
       
        $dados['vendedor_id'] = Auth::id();

        Produto::create($dados);

        return redirect()->route('produtos.index');
    }
}
