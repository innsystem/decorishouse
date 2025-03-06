<form id="form-request-services">
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="title" class="col-sm-12">Título do Serviço:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="title" name="title" placeholder="Digite o título do serviço" value="{{ isset($result->title) ? $result->title : '' }}">
            </div>
        </div>
        <div class="form-group mb-3 d-none">
            <label for="slug" class="col-sm-12">URL Amigável:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="slug" name="slug" placeholder="Digite o URL Amigável" value="{{ isset($result->slug) ? $result->slug : '' }}">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="description" class="col-sm-12">Descrição do Serviço:</label>
            <div class="col-sm-12">
                <textarea class="form-control" id="description" name="description" placeholder="Digite o description" style="display: none;">{{ isset($result->description) ? $result->description : '' }}</textarea>
                <div class="snow-editor" style="height: 250px;"></div>
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="status" class="col-sm-12">Status:</label>
            <div class="col-sm-12">
                <select name="status" id="status" class="form-select">
                    @foreach($statuses as $status)
                    <option value="{{$status->id}}" @if (isset($result->status) && $result->status == $status->id) selected @endif>{{$status->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="sort_order" class="col-sm-12">Posição de Exibição:</label>
            <div class="col-sm-12">
                <input type="number" class="form-control" id="sort_order" name="sort_order" placeholder="Digite o sort_order" value="{{ isset($result->sort_order) ? $result->sort_order : '0' }}">
            </div>
        </div>
    </div>
    <div class="bg-gray modal-footer justify-content-between">
        <button type="button" class="btn btn-success button-services-save"><i class="fa fa-check"></i> Salvar</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Fechar">Fechar</button>
    </div>
</form>