@extends('admin.base')

@section('title', 'Bem-vindos')

@section('content')
<div class="container">
    <div class="py-2 gap-2 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">@yield('title')</h4>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Métricas Principais -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Visitas Hoje</h5>
                    <p class="card-text">1,200</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Usuários Ativos</h5>
                    <p class="card-text">300</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Novos Usuários</h5>
                    <p class="card-text">50</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Taxa de Rejeição</h5>
                    <p class="card-text">20%</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Gráfico de Acessos dos Últimos 7 Dias -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Acessos nos Últimos 7 Dias</h5>
                    <canvas id="accessChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageMODAL')
@endsection

@section('pageCSS')
<!-- Adicione o CSS do Chart.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.css">
@endsection

@section('pageJS')
<!-- Adicione o JS do Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('accessChart').getContext('2d');
        var accessChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Dia 1', 'Dia 2', 'Dia 3', 'Dia 4', 'Dia 5', 'Dia 6', 'Dia 7'],
                datasets: [{
                    label: 'Acessos',
                    data: [120, 150, 180, 200, 170, 220, 250],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
