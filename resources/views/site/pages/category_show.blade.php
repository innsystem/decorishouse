@extends('site.base')

@section('title', $category->name)

@section('content')
<section class="recommended py-60">
    <div class="container container-lg">
        <div class="section-heading flex-between flex-wrap gap-16">
            <h5 class="mb-0">@yield('title')</h5>
            @if($category->children->isNotEmpty())
            <p class="text-muted">Mostrando produtos desta categoria e suas subcategorias</p>
            @endif
        </div>

        @if(count($products) > 0)
        <div class="row g-12">
            @foreach($products as $product)
            @if($product->affiliateLinks->isNotEmpty())
            @php $product_link = $product->affiliateLinks->first(); @endphp
            <div class="col-xxl-2 col-lg-3 col-sm-4 col-6">
                <div class="product-card h-100 p-8 border border-gray-100 hover-border-main-600 rounded-16 position-relative transition-2">
                    <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href product-card__thumb flex-center">
                        <img src="{{ asset('/galerias/img_loading.gif') }}" data-src="{{ $product->images[0] }}" alt="" class="lazy-load" loading="lazy">
                    </a>
                    <div class="product-card__content p-sm-2">
                        <h6 class="title text-lg fw-semibold mt-12 mb-8">
                            <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href link text-line-2">{{$product->name}}</a>
                        </h6>
                        <div class="flex-align gap-4">
                            <span class="text-main-600 text-md d-flex"><img src="{{ asset('/galerias/img_loading.gif') }}" data-src="{{ asset('/galerias/icons/marketplaces/'.$product_link->integration->slug.'.png') }}" alt="" class="avatar-marketplace lazy-load" loading="lazy"></span>
                            <span class="text-gray-500 text-xs"><b>{{$product_link->integration->name}}</b> <i class="text-muted">{{ $product->created_at->diffForHumans() }}</i></span>
                        </div>

                        <div class="product-card__price mb-8 d-flex align-items-center gap-8 mt-10">
                            @if($product->price_promotion > $product->price)
                            <span class="text-heading text-md fw-semibold">
                                R$ {{ number_format($product->price, 2, ',', '.') }}
                            </span>
                            <span class="text-gray-500 fw-normal">~</span>
                            @endif

                            <span class="text-heading text-md fw-semibold">
                                R$ {{ number_format($product->price_promotion, 2, ',', '.') }}
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
            @endif
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="row mt-32">
            <div class="col-12 d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @else
        <div class="alert alert-info">
            Nenhum produto encontrado para esta categoria.
        </div>
        @endif
    </div>
</section>
@endsection

@section('pageMODAL')
@endsection

@section('pageCSS')
<style>
    /* Estilos para a paginação */
    .pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        gap: 0.5rem;
    }
    
    .page-item {
        margin: 0 2px;
    }
    
    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        min-width: 36px;
        padding: 0 10px;
        border-radius: 50%;
        color: #333;
        background-color: #f5f5f5;
        transition: all 0.3s ease;
    }
    
    .page-item.active .page-link {
        color: #fff;
        background-color: var(--bs-main-600);
    }
    
    .page-item.disabled .page-link {
        color: #999;
        pointer-events: none;
    }
    
    .page-link:hover {
        background-color: var(--bs-main-100);
        color: var(--bs-main-600);
    }
</style>
@endsection

@section('pageJS')
<script>
    // Lazy loading para imagens
    document.addEventListener("DOMContentLoaded", function() {
        const lazyImages = [].slice.call(document.querySelectorAll("img.lazy-load"));
        
        if ("IntersectionObserver" in window) {
            let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove("lazy-load");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });
            
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback para navegadores que não suportam IntersectionObserver
            let active = false;
            
            const lazyLoad = function() {
                if (active === false) {
                    active = true;
                    
                    setTimeout(function() {
                        lazyImages.forEach(function(lazyImage) {
                            if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== "none") {
                                lazyImage.src = lazyImage.dataset.src;
                                lazyImage.classList.remove("lazy-load");
                                
                                lazyImages = lazyImages.filter(function(image) {
                                    return image !== lazyImage;
                                });
                                
                                if (lazyImages.length === 0) {
                                    document.removeEventListener("scroll", lazyLoad);
                                    window.removeEventListener("resize", lazyLoad);
                                    window.removeEventListener("orientationchange", lazyLoad);
                                }
                            }
                        });
                        
                        active = false;
                    }, 200);
                }
            };
            
            document.addEventListener("scroll", lazyLoad);
            window.addEventListener("resize", lazyLoad);
            window.addEventListener("orientationchange", lazyLoad);
        }
    });

    // Rastreamento de cliques em produtos
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