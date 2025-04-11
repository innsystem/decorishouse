@extends('admin.base')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Início</a></li>
                            <li class="breadcrumb-item active">Registros de Log</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Registros de Log do Sistema</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="header-title">Arquivo: laravel.log</h4>
                            </div>
                            <div class="col-6 text-end">
                                <form action="{{ route('admin.logs.clear') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja limpar o arquivo de log?')">
                                        <i class="ri-delete-bin-line me-1"></i> Limpar Log
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (!$logExists)
                            <div class="alert alert-warning" role="alert">
                                O arquivo de log não foi encontrado.
                            </div>
                        @elseif (empty($logContent))
                            <div class="alert alert-info" role="alert">
                                O arquivo de log está vazio.
                            </div>
                        @else
                            <div class="log-container">
                                <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; font-size: 12px;">{{ $logContent }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 