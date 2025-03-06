<form id="form-request-permissions">
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="title" class="col-sm-12">Título:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="title" name="title" placeholder="Digite o título" value="{{ isset($result->title) ? $result->title : '' }}">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="key" class="col-sm-12">Route (admin.module.function):</label>
            <div class="col-sm-12">
                <select name="key" id="key" class="form-select">
                    @foreach($routes as $route)
                    <option value="{{$route['uri']}}" {{ isset($result->key) && $result->key == $route['uri'] ? 'selected' : '' }}>{{$route['uri']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="bg-gray modal-footer justify-content-between">
        <button type="button" class="btn btn-success button-permissions-save"><i class="fa fa-check"></i> Salvar</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Fechar">Fechar</button>
    </div>
</form>