@extends('site.base')

@section('title', $category->name)

@section('content')
<section class="recommended py-60">
    <div class="container container-lg">
        <div class="section-heading flex-between flex-wrap gap-16">
            <h5 class="mb-0">@yield('title')</h5>
        </div>

        <div class="row g-12">
            @foreach($products as $product)
            @foreach($product->affiliateLinks as $product_link)
            <div class="col-xxl-2 col-lg-3 col-sm-4 col-6">
                <div class="product-card h-100 p-8 border border-gray-100 hover-border-main-600 rounded-16 position-relative transition-2">
                    <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href product-card__thumb flex-center">
                        <img src="{{ $product_link->product->images[0] }}" alt="">
                    </a>
                    <div class="product-card__content p-sm-2">
                        <h6 class="title text-lg fw-semibold mt-12 mb-8">
                            <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href link text-line-2">{{$product_link->product->name}}</a>
                        </h6>
                        <div class="flex-align gap-4">
                            <span class="text-main-600 text-md d-flex"><img src="{{ asset('/galerias/icons/marketplaces/'.$product_link->integration->slug.'.png') }}" alt="" class="avatar-marketplace"></span>
                            <span class="text-gray-500 text-xs"><b>{{$product_link->integration->name}}</b> <i class="text-muted">{{ $product_link->product->created_at->diffForHumans() }}</i></span>
                        </div>

                        <div class="product-card__price mb-8 d-flex align-items-center gap-8 mt-10">
                            @if($product_link->product->price_promotion > $product_link->product->price)
                            <span class="text-heading text-md fw-semibold">
                                R$ {{ number_format($product_link->product->price, 2, ',', '.') }}
                            </span>
                            <span class="text-gray-500 fw-normal">~</span>
                            @endif

                            <span class="text-heading text-md fw-semibold">
                                R$ {{ number_format($product_link->product->price_promotion, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="product-card__content mt-12">
                            <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href product-card__cart btn bg-main-50 text-main-600 hover-bg-main-600 hover-text-white py-11 px-24 rounded-pill flex-align gap-8 mt-24 w-100 justify-content-center">
                                Ver Promoção <i class="ph ph-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>
</section>
@endsection

@section('pageMODAL')
@endsection

@section('pageCSS')
@endsection

@section('pageJS')
<script>
    $(document).on('click', '.product-link-href', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        let productLinkId = $(this).data('product-link-id');
        let affiliateLink = $(this).data('affiliate-link');

        $.ajax({
            url: "{{ route('site.product.trackClick') }}",
            type: "POST",
            data: {
                product_link_id: productLinkId,
            },
            success: function(response) {
                window.open(affiliateLink, '_blank');
            },
            error: function(xhr) {
                console.error("Erro ao registrar clique", xhr);
            }
        });
    });
</script>
@endsection