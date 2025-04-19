@extends('site.base')

@section('title', $getSettings['meta_description'])

@section('content')
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

@if($testimonials->isNotEmpty())
<!-- Seção de Depoimentos -->
<section class="testimonials py-80 bg-light">
    <div class="container">
        <div class="section-heading text-center mb-40">
            <h2 class="fw-bold mb-2">O Que Nossos Clientes Dizem</h2>
            <p class="text-muted">Depoimentos de quem já comprou e adorou</p>
        </div>
        
        <div class="swiper testimonial-swiper">
            <div class="swiper-wrapper">
                @foreach($testimonials as $testimonial)
                <div class="swiper-slide">
                    <div class="testimonial-card bg-white p-24 rounded-16 shadow-sm position-relative h-100">
                        <div class="quote-icon position-absolute text-main-200">
                            <i class="ph ph-quotes fs-1"></i>
                        </div>
                        <div class="testimonial-content mt-30 mb-20">
                            <p class="testimonial-text text-gray-700">{{ $testimonial->content }}</p>
                        </div>
                        <div class="testimonial-author d-flex align-items-center">
                            <div class="testimonial-avatar me-3">
                                @if($testimonial->avatar)
                                    <img src="{{ $testimonial->avatar }}" alt="{{ $testimonial->name }}" class="rounded-circle" width="60" height="60">
                                @else
                                    <div class="avatar-placeholder rounded-circle bg-main-100 text-main-600 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <span class="fw-bold">{{ substr($testimonial->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="testimonial-info">
                                <h6 class="testimonial-name fw-bold mb-1">{{ $testimonial->name }}</h6>
                                <p class="testimonial-location text-muted mb-1">{{ $testimonial->localization }}</p>
                                <div class="testimonial-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ph {{ $i <= $testimonial->rating ? 'ph-star-fill text-warning' : 'ph-star text-gray-300' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination mt-30"></div>
        </div>
    </div>
</section>
@endif
@endsection

@section('pageMODAL')
@endsection

@section('pageCSS')
<!-- Swiper CSS -->
<link rel="stylesheet" href="{{ asset('/plugins/swiper/swiper-bundle.min.css') }}">

<style>
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
    
    /* Estilos para os depoimentos */
    .testimonials {
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
    }
    
    .testimonials::before {
        content: '';
        position: absolute;
        top: -100px;
        left: 0;
        width: 100%;
        height: 100px;
        background: linear-gradient(to bottom right, transparent 49%, #f8f9fa 50%);
    }
    
    .py-80 {
        padding-top: 80px;
        padding-bottom: 80px;
    }
    
    .testimonial-card {
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .testimonial-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .quote-icon {
        top: 20px;
        right: 20px;
        opacity: 0.2;
    }
    
    .testimonial-text {
        font-size: 1rem;
        line-height: 1.6;
        min-height: 80px;
    }
    
    .mb-40 {
        margin-bottom: 40px;
    }
    
    .testimonial-swiper {
        padding-bottom: 50px;
    }
    
    @media (max-width: 768px) {
        .py-80 {
            padding-top: 50px;
            padding-bottom: 50px;
        }
        
        .testimonial-text {
            min-height: auto;
        }
    }
</style>
@endsection

@section('pageJS')
<!-- Swiper JS -->
<script src="{{ asset('/plugins/swiper/swiper-bundle.min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicialização do Swiper de Depoimentos
        var testimonialSwiper = new Swiper('.testimonial-swiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            pagination: {
                el: '.testimonial-swiper .swiper-pagination',
                clickable: true,
            },
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            }
        });
        
        // Lazy loading das imagens
        var lazyLoadImages = document.querySelectorAll('.lazy-load');
        
        if ('IntersectionObserver' in window) {
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
            
            lazyLoadImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback para navegadores que não suportam IntersectionObserver
            let active = false;
            
            const lazyLoad = function() {
                if (active === false) {
                    active = true;
                    
                    setTimeout(function() {
                        lazyLoadImages.forEach(function(lazyImage) {
                            if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== "none") {
                                lazyImage.src = lazyImage.dataset.src;
                                lazyImage.classList.remove("lazy-load");
                                
                                lazyLoadImages = Array.from(lazyLoadImages).filter(function(image) {
                                    return image !== lazyImage;
                                });
                                
                                if (lazyLoadImages.length === 0) {
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
    
    // Registro de cliques em produtos
    $('.product-link-href').on('click', function(e) {
        e.preventDefault();
        
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