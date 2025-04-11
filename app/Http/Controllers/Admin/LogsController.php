<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LogsController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        $logExists = File::exists($logPath);
        $logContent = '';
        
        if ($logExists) {
            // Limitar a leitura para evitar problemas com arquivos muito grandes
            $logContent = File::size($logPath) > 5242880 
                ? "Arquivo de log muito grande para exibição. Use a função de limpeza e verifique novamente."
                : File::get($logPath);
        }

        return view('admin.pages.logs.index', compact('logContent', 'logExists'));
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            // Limpar o arquivo mantendo-o vazio em vez de excluí-lo
            File::put($logPath, '');
            return redirect()->route('admin.logs.index')->with('success', 'Arquivo de log limpo com sucesso!');
        }
        
        return redirect()->route('admin.logs.index')->with('error', 'Arquivo de log não encontrado.');
    }
} 