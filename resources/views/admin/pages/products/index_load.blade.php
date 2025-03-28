@if(isset($results) && count($results) > 0)
@foreach($results as $product)
<div id="row_product_{{$product->id}}" class="col-12 pb-2 mb-4 border-bottom rounded">
    <div class="d-flex flex-wrap gap-3 align-items-center">
        {{-- Exibir a primeira imagem do produto --}}
        <div>
            @php
            $images = $product->images ?? [];
            $firstImage = $images[0] ?? 'https://via.placeholder.com/100'; // Imagem placeholder se não houver
            @endphp
            <img src="{{ $firstImage }}" alt="{{ $product->name }}" class="rounded" width="80">
        </div>

        {{-- Informações do produto --}}
        <div class="flex-grow-1">
            <h5 class="h5 mb-1 fw-bold">{{ Str::limit($product->name, '40', '...') }}</h5>
            <p class="mb-1 d-none">
                <span class="text-muted">Preço: </span>
                <strong class="text-dark">{{ number_format($product->price, 2, ',', '.') }}</strong>
                @if($product->price_promotion)
                <span class="text-success ms-2">Promoção:
                    <strong>{{ number_format($product->price_promotion, 2, ',', '.') }}</strong>
                </span>
                @endif
            </p>
            <p class="mb-1">
                <span class="text-muted">Links</span>
                @if($product->affiliateLinks)
                @foreach($product->affiliateLinks as $link)
                <a href="{{ $link->affiliate_link }}" target="_blank" class="btn btn-sm btn-outline-primary fs-7 py-0 px-1 ms-2">{{ $link->integration->name }}</a>
                @endforeach
                @endif
            </p>

            {{-- Exibir categorias relacionadas --}}
            <p class="mb-0">
                @foreach($product->categories as $category)
                <span class="badge bg-primary">{{ $category->name }}</span>
                @endforeach
            </p>
        </div>

        {{-- Botões de ação --}}
        <div>
            <button type="button" class="btn btn-sm btn-secondary button-products-generate-image-feed" data-product-id="{{$product->id}}">
                <i class="fab fa-facebook me-1"></i> <i class="fab fa-instagram"></i> Postar Feed
            </button>
            <button type="button" class="btn btn-sm btn-secondary button-products-generate-image" data-product-id="{{$product->id}}">
                <i class="fas fa-magic"></i> Gerar Story
            </button>
            <button type="button" class="btn btn-sm btn-info button-products-edit" data-product-id="{{$product->id}}">
                <i class="fa fa-edit"></i> Editar
            </button>
            <button type="button" class="btn btn-sm btn-danger button-products-delete" data-product-id="{{$product->id}}" data-product-name="{{$product->name}}">
                <i class="fa fa-trash"></i> Apagar
            </button>
        </div>
    </div>
</div><!-- col-12 -->
@endforeach
@else
<div class="alert alert-warning mb-0">Nenhum resultado foi localizado...</div>
@endif