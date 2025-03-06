@extends('site.base')

@section('title', 'Página Inicial')

@section('content')
<h1>@yield('title')</h1>
<hr>
@if(isset($pages) && count($pages) > 0)
<div>
    <p><b>Páginas</b></p>
    <ul>
        @foreach($pages as $page)
        <li><a href="{{route('site.pages.show', [$page->slug])}}">{{$page->title}}</a></li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($services) && count($services) > 0)
<div>
    <p><b>Serviços</b></p>
    <ul>
        @foreach($services as $service)
        <li><a href="{{route('site.services.show', [$service->slug])}}">{{$service->title}}</a></li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($portfolios) && count($portfolios) > 0)
<div>
    <p><b>Portfólios</b></p>
    <ul>
        @foreach($portfolios as $portfolio)
        <li><a href="{{route('site.portfolios.show', [$portfolio->slug])}}">{{$portfolio->title}}</a></li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($testimonials) && count($testimonials) > 0)
<div>
    <p><b>Depoimentos</b></p>
    <ul>
        @foreach($testimonials as $testimonial)
        <li>
            <img src="{{ $testimonial->image ? asset('storage/' . $testimonial->image) : asset('galerias/avatares/sem_foto.jpg') }}" alt="{{$testimonial->name}}" style="width:50px;height:50px;">
            {{$testimonial->name}} - {{ Str::limit($testimonial->content, 80) }}
            <div>
                @for ($i = 0; $i < 5; $i++)
                    @if ($i < $testimonial->rating)
                        ★
                    @else
                        ☆
                    @endif
                @endfor
            </div>
        </li>
        @endforeach
    </ul>
</div>
@endif

@section('pageMODAL')
@endsection

@section('pageJS')
@endsection

@section('pageCSS')
@endsection