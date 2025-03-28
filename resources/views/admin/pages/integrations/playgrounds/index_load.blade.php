@if(isset($shopeeOffers) && count($shopeeOffers) > 0)
<h3>Ofertas Shopee</h3>
<div class="row">
    @foreach($shopeeOffers as $item)
    <div class="col-6 col-md-4 col-lg-3 mb-4">
        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-fluid border rounded mb-2 p-1">

        <p class="mb-1 fs-7 fw-bold">{{ $item['name'] }}</p>
        <p class="mb-1 fs-7 fw-bold">CategoryId: {{ $item['category_id'] }}</p>
        <p class="mb-0 fs-7 text-muted">Comissão: {{ $item['commission'] * 100 }}%</p>
        <p class="mb-0 fs-7 text-muted">Válido de {{ $item['period_start'] }} até {{ $item['period_end'] }}</p>
        <div class="d-flex gap-3 mt-2">
            <a href="{{ $item['offer_link'] }}" class="btn btn-sm btn-primary fs-7 p-1" target="_Blank"><i class="fas fa-link"></i> Link</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(isset($shopOffers) && count($shopOffers) > 0)
<h3>Ofertas de Lojas</h3>
<div class="row">
    @foreach($shopOffers as $shop)
    <div class="col-6 col-md-4 col-lg-3 mb-4">
        <img src="{{ $shop['image'] }}" alt="{{ $shop['shop_name'] }}" class="img-fluid border rounded mb-2 p-1">

        <p class="mb-1 fs-7 fw-bold">{{ $shop['shop_name'] }}</p>
        <p class="mb-0 fs-7 text-muted">Comissão: {{ $shop['commission'] * 100 }}%</p>
        <p class="mb-0 fs-7 text-muted">Avaliação: {{ $shop['rating'] }} ⭐</p>
        <p class="mb-0 fs-7 text-muted">Orçamento: {{ $shop['remaining_budget'] }}</p>
        <p class="mb-0 fs-7 text-muted">Válido de {{ $shop['period_start'] }} até {{ $shop['period_end'] }}</p>
        <div class="d-flex gap-3 mt-2">
            <a href="{{ $shop['offer_link'] }}" class="btn btn-sm btn-primary fs-7 p-1" target="_Blank"><i class="fas fa-link"></i> Link</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@if(isset($productOffers) && count($productOffers) > 0)
<h3>Produtos em Oferta</h3>
<div class="row">
    @foreach($productOffers as $item)
    <div class="col-6 col-md-4 col-lg-3 mb-4">
        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-fluid border rounded mb-2 p-1">

        <p class="mb-1 fs-7 fw-bold">{{ $item['name'] }}</p>
        <p class="mb-1 fs-7 fw-bold">ID: {{ $item['id'] }}</p>
        <p class="mb-0 fs-7 text-muted">Preço: R$ {{ $item['price_min'] }} - R$ {{ $item['price_max'] }}</p>
        <p class="mb-0 fs-7 text-muted">Comissão: {{ $item['commission'] * 100 }}%</p>
        <div class="d-flex gap-3 mt-2">
            <a href="{{ $item['offer_link'] }}" class="btn btn-sm btn-primary fs-7 p-1" target="_Blank"><i class="fas fa-link"></i> Link</a>
        </div>
    </div>
    @endforeach
</div>
@endif



@if(empty($shopeeOffers) && empty($shopOffers) && empty($productOffers))
<div class="alert alert-warning mb-0">Nenhum resultado foi localizado...</div>
@endif