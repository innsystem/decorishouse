<form id="form-request-customers">
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="name" class="col-sm-12">Nome do Usuário:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="name" name="name" placeholder="Digite o name" value="{{ isset($result->name) ? $result->name : '' }}">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="email" class="col-sm-12">E-mail de Acesso:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="email" name="email" placeholder="Digite o email" value="{{ isset($result->email) ? $result->email : '' }}">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="password" class="col-sm-12">Senha de Acesso:</label>
            <div class="col-sm-12">
                <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha de acesso" value="">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="document" class="col-sm-12">Documento CPF/CNPJ:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="document" name="document" placeholder="Digite o Documento CPF/CNPJ" value="{{ isset($result->document) ? $result->document : '' }}">
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="phone" class="col-sm-12">Telefone:</label>
            <div class="col-sm-12">
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Digite o Telefone" value="{{ isset($result->phone) ? $result->phone : '' }}">
            </div>
        </div>
    </div>
    <div class="bg-gray modal-footer justify-content-between">
        <button type="button" class="btn btn-success button-customers-save"><i class="fa fa-check"></i> Salvar</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas" aria-label="Fechar">Fechar</button>
    </div>
</form>