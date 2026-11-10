<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Alterado o nome para 'create' para corrigir o erro de rota do projeto
    public function create()
    {
        return view('auth.login');
    }

    // R2: Implementa a tentativa de login (Auth::attempt)
    public function attempt(Request $request)
    {
        // Valida se os campos foram preenchidos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tenta autenticar o usuário
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Se der certo, redireciona para a listagem de produtos
            return redirect()->intended('/produtos');
        }

        // Se as credenciais forem inválidas, volta ao login com mensagem de erro
        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    // R2: Implementa o encerramento da sessão (Logout)
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/produtos');
    }
}
