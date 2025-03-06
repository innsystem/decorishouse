@if(isset($results) && count($results) > 0)
@foreach($results as $customer)
<div id="row_customer_{{$customer->id}}" class="col-12 pb-2 mb-4 border-bottom rounded">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="flex-grow-1 d-flex align-items-center">
            <div>
                <a href="{{ route('admin.customers.show', [$customer->id]) }}"><img src="{{ asset('/galerias/avatares/innsystem.png') }}" alt="{{$customer->name}}" class="avatar-xs rounded-circle me-1"></a>
            </div>
            <div>
                <h5 class="h5 mb-0 fw-bold"><a href="{{ route('admin.customers.show', [$customer->id]) }}">{{$customer->name}}</a> </h5>
                <p class="mb-0"><span class="fw-normal fs-7">({{$customer->email}})</span></p>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.customers.show', [$customer->id]) }}" class="btn btn-sm btn-primary">Detalhes</a>
            <button type="button" class="btn btn-sm btn-info button-customers-edit" data-customer-id="{{$customer->id}}">Editar</button>
            @if($customer->id != 1 && $customer->id != Auth::user()->id)
            <button type="button" class="btn btn-sm btn-danger button-customers-delete" data-customer-id="{{$customer->id}}" data-customer-name="{{$customer->name}}">Apagar</button>
            @endif
        </div>
    </div>
</div><!-- col-12 -->
@endforeach
@else
<div class="alert alert-warning mb-0">Nenhum resultado foi localizado...</div>
@endif