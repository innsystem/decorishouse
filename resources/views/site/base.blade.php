<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('title')">
    <!-- Title-->
    <title>@yield('title') | {{$getSettings['site_name']}}</title>

    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph (OG) Meta Tags -->
    <meta property="og:title" content="@yield('title', $getSettings['site_name'])">
    <meta property="og:description" content="@yield('description', $getSettings['meta_description'])">
    <meta property="og:image" content="@yield('image', asset('/galerias/facebook_decorishouse.png'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{$getSettings['site_name']}}">
    <meta property="og:locale" content="pt_BR">

    <!-- SEO Meta Tags -->
    <meta name="keywords" content="@yield('keywords', $getSettings['meta_keywords'])">
    <meta name="description" content="@yield('description', $getSettings['meta_description'])">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $getSettings['site_name'])">
    <meta name="twitter:description" content="@yield('description', $getSettings['meta_description'])">
    <meta name="twitter:image" content="@yield('image', asset($getSettings['logo']))">

    <!-- Favicon-->
    <link rel="shortcut icon" href="{{ asset('/galerias/favicon.ico?3') }}" type="image/x-icon">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/bootstrap.min.css') }}">
    <!-- select 2 -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/select2.min.css') }}">
    <!-- Slick -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/slick.css') }}">
    <!-- Wow -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/jquery-ui.css') }}">
    <!-- Main css -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/main.css?1') }}">
    <!-- Templace Custom -->
    <link rel="stylesheet" href="{{ asset('/tpl_site/css/template_custom.css?1') }}">
    <!-- Font Awesome -->
    <link href="{{ asset('/plugins/fontawesome/css/all.min.css') }}" rel="stylesheet">

    @yield('pageCSS')

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZH92GES8SL"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-ZH92GES8SL');
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NSHVD8R6');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NSHVD8R6"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div class="overlay"></div>
    <div class="side-overlay"></div>
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>

    <div class="header-top bg-main-600 flex-between" style="min-height: 25px;">
        <div class="container container-lg">
            <div class="flex-between flex-wrap gap-8">

            </div>
        </div>
    </div>

    <header class="header-middle border-bottom border-gray-100">
        <div class="container container-lg">
            <nav class="header-inner flex-between">
                <!-- Logo Start -->
                <div class="logo">
                    <a href="{{ route('site.index') }}" class="link">
                        <img src="{{ asset('/galerias/logo_white.png') }}" alt="Logo">
                    </a>
                </div>
                <!-- Logo End  -->

                <!-- form location Start -->
                <form action="#" class="flex-align flex-wrap form-location-wrapper">
                    <div class="search-category d-flex h-48 select-border-end-0 radius-end-0 search-form d-sm-flex d-none">
                        <div class="search-form__wrapper position-relative">
                            <input type="text" id="search-input" class="search-form__input common-input py-13 ps-16 pe-18 rounded-end-pill pe-44" placeholder="Procure por nome do produto">
                            <button type="button" class="w-32 h-32 bg-main-600 rounded-circle flex-center text-xl text-white position-absolute top-50 translate-middle-y inset-inline-end-0 me-8">
                                <i class="ph ph-magnifying-glass"></i>
                            </button>
                            <div id="search-results" class="search-results-container"></div>
                        </div>
                    </div>
                </form>
                <!-- form location start -->

                <ul class="flex-align justify-content-center gap-16 text-center">
                    @if(isset($getSettings['facebook']) && $getSettings['facebook'] != '')
                    <li>
                        <a href="{{ $getSettings['facebook'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                            <i class="ph-fill ph-facebook-logo"></i>
                        </a>
                    </li>
                    @endif
                    @if(isset($getSettings['instagram']) && $getSettings['instagram'] != '')
                    <li>
                        <a href="{{ $getSettings['instagram'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                            <i class="ph-fill ph-instagram-logo"></i>
                        </a>
                    </li>
                    @endif
                    @if(isset($getSettings['tiktok']) && $getSettings['tiktok'] != '')
                    <li>
                        <a href="{{ $getSettings['tiktok'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                            <i class="ph-fill ph-tiktok-logo"></i>
                        </a>
                    </li>
                    @endif
                    @if(isset($getSettings['youtube']) && $getSettings['youtube'] != '')
                    <li>
                        <a href="{{ $getSettings['youtube'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                            <i class="ph-fill ph-youtube-logo"></i>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>

    <header class="header bg-white border-bottom border-gray-100">
        <div class="container container-lg">
            <nav class="header-inner d-flex justify-content-between gap-8">
                <div class="flex-align menu-category-wrapper">

                    <!-- Category Dropdown Start -->
                    <div class="category on-hover-item">
                        <button type="button" class="category__button flex-align gap-8 fw-medium p-16 border-end border-start border-gray-100 text-heading">
                            <span class="icon text-2xl d-xs-flex d-none"><i class="ph ph-dots-nine"></i></span>
                            <span class="d-sm-flex d-none">Todas</span> Categorias
                            <span class="arrow-icon text-xl d-flex"><i class="ph ph-caret-down"></i></span>
                        </button>

                        <div class="responsive-dropdown on-hover-dropdown common-dropdown nav-submenu p-0 submenus-submenu-wrapper">

                            <button type="button" class="close-responsive-dropdown rounded-circle text-xl position-absolute inset-inline-end-0 inset-block-start-0 mt-4 me-8 d-lg-none d-flex"> <i class="ph ph-x"></i> </button>

                            <!-- Logo Start -->
                            <div class="logo px-16 d-lg-none d-block">
                                <a href="{{ route('site.index') }}" class="link">
                                    <img src="{{ asset('/galerias/logo_white.png') }}" alt="Logo">
                                </a>
                            </div>
                            <!-- Logo End -->

                            <ul class="scroll-sm p-0 py-8 w-300 max-h-400 overflow-y-auto">
                                @if(isset($getCategories) && count($getCategories) > 0)
                                @foreach($getCategories as $category)
                                @if($category->children->isNotEmpty())
                                <li class="has-submenus-submenu">
                                    <a href="javascript:void(0)" class="text-gray-500 text-15 py-12 px-16 flex-align gap-8 rounded-0">
                                        <span>{{$category->name}}</span>
                                        <span class="icon text-md d-flex ms-auto"><i class="ph ph-caret-right"></i></span>
                                    </a>

                                    <div class="submenus-submenu py-16">
                                        <h6 class="text-lg px-16 submenus-submenu__title">{{$category->name}}</h6>
                                        <ul class="submenus-submenu__list max-h-300 overflow-y-auto scroll-sm">
                                            @foreach($category->children as $child)
                                            <li>
                                                <a href="{{ route('site.category.show', $child->slug) }}">{{$child->name}}</a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                                @else
                                <li class="nav-menu__item">
                                    <a href="{{ route('site.category.show', $category->slug) }}" class="nav-menu__link">{{$category->name}}</a>
                                </li>
                                @endif
                                @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <!-- Category Dropdown End  -->

                    <!-- Menu Start  -->
                    <div class="header-menu d-lg-block d-none">
                        <!-- Nav Menu Start -->
                        <ul class="nav-menu flex-align ">
                            @if(isset($getCategories) && count($getCategories) > 0)
                            @foreach($getCategories as $category)
                            @if($category->children->isNotEmpty())
                            <li class="on-hover-item nav-menu__item has-submenu">
                                <a href="javascript:void(0)" class="nav-menu__link">{{$category->name}}</a>
                                <ul class="on-hover-dropdown common-dropdown nav-submenu scroll-sm">
                                    @foreach($category->children as $child)
                                    <li class="common-dropdown__item nav-submenu__item">
                                        <a href="{{ route('site.category.show', $child->slug) }}" class="common-dropdown__link nav-submenu__link hover-bg-neutral-100">
                                            {{$child->name}}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            @else
                            <li class="nav-menu__item">
                                <a href="{{ route('site.category.show', $category->slug) }}" class="nav-menu__link">{{$category->name}}</a>
                            </li>
                            @endif
                            @endforeach
                            @endif
                            <!-- <li class="nav-menu__item">
                                <a href="#" class="nav-menu__link">Blog</a>
                            </li> -->
                        </ul>
                        <!-- Nav Menu End -->
                    </div>
                    <!-- Menu End  -->
                </div>
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="footer py-60">
        <div class="container container-lg">
            <div class="footer-item-wrapper d-flex flex-wrap align-items-start justify-content-center">
                <div class="col-12 text-center footer-logo">
                    <img src="{{ asset('/galerias/logo_white.png') }}" alt="" class="img-fluid" style="max-width:220px; margin-bottom:15px;">
                </div>
                <div class="col-12 text-center footer-item">
                    <ul class="flex-align justify-content-center gap-16 text-center">
                        @if(isset($getSettings['facebook']) && $getSettings['facebook'] != '')
                        <li>
                            <a href="{{ $getSettings['facebook'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                                <i class="ph-fill ph-facebook-logo"></i>
                            </a>
                        </li>
                        @endif
                        @if(isset($getSettings['instagram']) && $getSettings['instagram'] != '')
                        <li>
                            <a href="{{ $getSettings['instagram'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                                <i class="ph-fill ph-instagram-logo"></i>
                            </a>
                        </li>
                        @endif
                        @if(isset($getSettings['tiktok']) && $getSettings['tiktok'] != '')
                        <li>
                            <a href="{{ $getSettings['tiktok'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                                <i class="ph-fill ph-tiktok-logo"></i>
                            </a>
                        </li>
                        @endif
                        @if(isset($getSettings['youtube']) && $getSettings['youtube'] != '')
                        <li>
                            <a href="{{ $getSettings['youtube'] }}" class="w-44 h-44 flex-center bg-main-100 text-main-600 text-xl rounded-circle hover-bg-main-600 hover-text-white">
                                <i class="ph-fill ph-youtube-logo"></i>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="col-12 text-center">
                    <p class="fs-7 mb-1">Como participante do Programa de Associados da Amazon, Shopee e Mercado Livre, <br> recebo comissão pelas compras qualificadas efetuadas por indicação.</p>
                    <p class="fs-7 mb-0">* Preços e disponibilidades estão sujeito a alterações a qualquer momento. sem aviso prévio.</p>
                </div>
            </div>
        </div>
    </footer>

    <div class="bottom-footer bg-color-one py-8">
        <div class="container container-lg">
            <div class="bottom-footer__inner flex-between flex-wrap gap-16 py-16">
                <p class="bottom-footer__text ">{{$getSettings['site_name']}} &copy; {{date('Y')}}. Todos os Direitos Reservas </p>
                <div class="flex-align gap-8 flex-wrap">
                    <span class="text-heading text-sm">Desenvolvido por</span>
                    <a href="https://innsystem.com.br" target="_Blank" class="fw-bold"><img src="{{ asset('/innsystem-logo-dark.png') }}" alt="InnSystem Inovação em Sistemas" style="max-width:92px;"></a>
                </div>
            </div>
        </div>
    </div>

    @yield('pageMODAL')

    <!-- Jquery js -->
    <script src="{{ asset('/tpl_site/js/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap Bundle Js -->
    <script src="{{ asset('/tpl_site/js/boostrap.bundle.min.js') }}"></script>
    <!-- Bootstrap Bundle Js -->
    <script src="{{ asset('/tpl_site/js/phosphor-icon.js') }}"></script>
    <!-- Select 2 -->
    <script src="{{ asset('/tpl_site/js/select2.min.js') }}"></script>
    <!-- Slick js -->
    <script src="{{ asset('/tpl_site/js/slick.min.js') }}"></script>
    <!-- Slick js -->
    <script src="{{ asset('/tpl_site/js/count-down.js') }}"></script>
    <!-- wow js -->
    <script src="{{ asset('/tpl_site/js/jquery-ui.js') }}"></script>

    <!-- main js -->
    <script src="{{ asset('/tpl_site/js/main.js') }}"></script>

    @yield('pageJS')

    <script>
        $(document).ready(function() {
            $('#search-input').on('input', function() {
                let query = $(this).val().trim();

                if (query.length < 3) {
                    $('#search-results').hide();
                    return;
                }

                $.ajax({
                    url: "{{ route('site.products.search') }}",
                    type: "GET",
                    data: {
                        query: query
                    },
                    success: function(data) {
                        let resultsContainer = $('#search-results');
                        resultsContainer.empty();

                        if (data.length > 0) {
                            let regex = new RegExp(`(${query})`, 'gi'); // Expressão regular para encontrar a palavra
                            data.forEach(function(product) {
                                let highlightedName = product.name.replace(regex, '<span class="highlight">$1</span>'); // Destaca a palavra-chave
                                resultsContainer.append(`<a href="${product.affiliate_link}" target="_Blank">${highlightedName}</a>`);
                            });

                            resultsContainer.show();
                        } else {
                            resultsContainer.hide();
                        }
                    }
                });
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-form__wrapper').length) {
                    $('#search-results').hide();
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const images = document.querySelectorAll('img.lazy-load');

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('data-src'); // Defina o src com o valor de data-src
                        img.classList.remove('lazy-load'); // Remova a classe de lazy-load
                        observer.unobserve(img); // Pare de observar a imagem
                    }
                });
            }, {
                threshold: 0.1 // A imagem será carregada quando 10% da sua área for visível
            });

            images.forEach(image => {
                observer.observe(image); // Comece a observar as imagens
            });
        });
    </script>
</body>

</html>