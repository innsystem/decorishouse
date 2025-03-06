@if(isset($results) && count($results) > 0)
@foreach($results as $page)
<div id="row_page_{{$page->id}}" class="col-12 pb-2 mb-4 border-bottom rounded">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="flex-grow-1 d-flex align-items-center">
            <div>
                <h5 class="h6 mb-1 fw-bold">{{$page->title}}</h5>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-info button-pages-edit" data-page-id="{{$page->id}}">Editar</button>
            <button type="button" class="btn btn-sm btn-danger button-pages-delete" data-page-id="{{$page->id}}" data-page-name="{{$page->name}}">Apagar</button>
        </div>
    </div>
</div><!-- col-12 -->
@endforeach
@else
<div class="alert alert-warning mb-0">Nenhum resultado foi localizado...</div>
@endif