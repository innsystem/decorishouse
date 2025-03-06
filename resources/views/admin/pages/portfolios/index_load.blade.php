@if(isset($results) && count($results) > 0)
@foreach($results as $portfolio)
<div id="row_portfolio_{{$portfolio->id}}" class="col-12 pb-2 mb-4 border-bottom rounded">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="flex-grow-1 d-flex align-items-center gap-2">
            <div>
                <img src="{{ $portfolio->featured_image }}" alt="Capa do Portfólio" class="avatar-sm border rounded" style="object-fit: cover;">
            </div>
            <div>
                <h5 class="h6 mb-1 fw-bold">{{$portfolio->title}}</h5>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-info button-portfolios-edit" data-portfolio-id="{{$portfolio->id}}">Editar</button>
            <button type="button" class="btn btn-sm btn-danger button-portfolios-delete" data-portfolio-id="{{$portfolio->id}}" data-portfolio-name="{{$portfolio->title}}">Apagar</button>
        </div>
    </div>
</div><!-- col-12 -->
@endforeach
@else
<div class="alert alert-warning mb-0">Nenhum resultado foi localizado...</div>
@endif