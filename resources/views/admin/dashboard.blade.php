@extends('admin.layout')
@section('title', 'Dashboard Admin')
@section('content')
<div class="content-wrapper p-4">
    <section class="content-header">
        <h1 class="mb-4">Dashboard Administrativo</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalRevenue ?? 0 }}</h3>
                        <p>Receita Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $refundedAmount ?? 0 }}</h3>
                        <p>Reembolsos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-undo"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalOrders ?? 0 }}</h3>
                        <p>Pedidos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $totalUsers ?? 0 }}</h3>
                        <p>Usuários</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Gráfico e outros dados podem ser adicionados aqui -->
    </section>
</div>
@endsection
