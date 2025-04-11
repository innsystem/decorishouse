<form id="form-request-integrations">
    <div class="modal-body">
        <h4 class="h4 mb-2">{{ isset($result->name) ? $result->name : '' }}</h4>

        <div class="form-group mb-3">
            <label for="access_token" class="col-sm-12">AcessToken:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="access_token" name="access_token" placeholder="Digite o AcessToken" value="{{ isset($result->settings['access_token']) ? $result->settings['access_token'] : '' }}">
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
    </div>
    <div class="bg-gray modal-footer justify-content-between">
        <button type="button" class="btn btn-success button-integrations-save"><i class="fa fa-check"></i> Salvar Alterações</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Fechar">Fechar</button>
    </div>
</form>