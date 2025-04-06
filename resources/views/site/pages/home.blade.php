@extends('site.base')

@section('title', 'Transforme sua Casa')

@section('content')
<!-- Slider Principal Fullscreen-->
<section class="main-slider">
    <div class="swiper main-swiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="slider-img">
                    <img src="{{ asset('/galerias/slider_home/slide_1.jpg') }}" alt="Decoração para Sala" class="w-100">
                    <div class="slider-content text-center">
                        <h2 class="slider-title text-white">Transforme sua Sala</h2>
                        <p class="slider-text text-white">Encontre os melhores itens para decorar sua sala com estilo e conforto</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="slider-img">
                    <img src="{{ asset('/galerias/loading.gif') }}" data-src="{{ asset('/galerias/slider_home/slide_2.jpg') }}" alt="Decoração para Cozinha" class="w-100 lazy-load" loading="lazy">
                    <div class="slider-content text-center">
                        <h2 class="slider-title text-white">Sua Cozinha dos Sonhos</h2>
                        <p class="slider-text text-white">Descubra itens práticos e elegantes para sua cozinha</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="slider-img">
                    <img src="{{ asset('/galerias/loading.gif') }}" data-src="{{ asset('/galerias/slider_home/slide_3.jpg') }}" alt="Decoração para Banheiro" class="w-100 lazy-load" loading="lazy">
                    <div class="slider-content text-center">
                        <h2 class="slider-title text-white">Banheiro Elegante</h2>
                        <p class="slider-text text-white">Encontre os melhores itens para decorar seu banheiro com conforto e estilo</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Seções de Produtos por Categoria -->
@foreach($categoriesWithProducts as $categoryData)
<section class="recommended py-60">
    <div class="container container-lg">
        <div class="section-heading flex-between flex-wrap gap-16">
            <h5 class="mb-0">{{ $categoryData['category']->name }}</h5>
            <a href="{{ route('site.category.show', $categoryData['category']->slug) }}" class="btn btn-outline-primary btn-sm">Ver Mais <i class="ph ph-arrow-right"></i></a>
        </div>

        <div class="row g-12">
            @foreach($categoryData['products'] as $product)
            @if($product->affiliateLinks->isNotEmpty())
            @php $product_link = $product->affiliateLinks->first(); @endphp
            <div class="col-xxl-3 col-lg-4 col-sm-6 col-6">
                <div class="product-card h-100 p-8 border border-gray-100 hover-border-main-600 rounded-16 position-relative transition-2">
                    <a href="{{ $product_link->affiliate_link }}" data-product-link-id="{{$product_link->id}}" data-affiliate-link="{{ $product_link->affiliate_link }}" class="product-link-href product-card__thumb flex-center">
                        <img src="{{ asset('/galerias/img_loading.gif') }}" data-src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="lazy-load" loading="lazy">
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
        
        <div class="mt-30 text-left">
            <a href="{{ route('site.category.show', $categoryData['category']->slug) }}" class="btn btn-categoria-ver-mais">Ver Todos os Produtos de {{ $categoryData['category']->name }}</a>
        </div>
    </div>
</section>
@endforeach

@endsection

@section('pageMODAL')
@endsection

@section('pageCSS')
<!-- Swiper CSS -->
<link rel="stylesheet" href="{{ asset('/plugins/swiper/swiper-bundle.min.css') }}">

<style>
    /* Estilos para o Slider Principal */
    .main-slider {
        margin-top: -1px;
    }
    .main-slider .swiper-slide {
        height: 80vh;
    }
    .slider-img {
        position: relative;
        height: 100%;
    }
    .slider-img img {
        height: 100%;
        object-fit: cover;
    }
    .slider-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        max-width: 800px;
    }
    .slider-title {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
    }
    .slider-text {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
    }
    
    /* Estilos para os boxes de categoria */
    .category-box {
        height: 250px;
        transition: all 0.3s ease;
    }
    .category-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .category-content {
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    }
    
    /* Botão Ver Mais */
    .section-heading .btn-outline-primary {
        border-color: var(--bs-main-600);
        color: var(--bs-main-600);
    }
    
    .section-heading .btn-outline-primary:hover {
        background-color: var(--bs-main-600);
        color: white;
    }
    
    /* Botão Ver Todos os Produtos */
    .btn-categoria-ver-mais {
        background-color: var(--bs-main-600) !important;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-categoria-ver-mais:hover {
        background-color: var(--bs-main-700) !important;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .mt-30 {
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .main-slider .swiper-slide {
            height: 60vh;
        }
        .slider-title {
            font-size: 2rem;
        }
        .slider-text {
            font-size: 1rem;
        }
    }
</style>
@endsection

@section('pageJS')
<!-- Swiper JS -->
<script src="{{ asset('/plugins/swiper/swiper-bundle.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // Inicializar o Swiper
        var mainSwiper = new Swiper('.main-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            autoplay: {
                delay: 10000, // 10 segundos entre slides
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            loop: true,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });
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