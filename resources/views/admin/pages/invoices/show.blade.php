@extends('admin.base')

@section('title', 'Fatura ' . $result->id)

@section('content')
<div class="container">
    <div class="py-2 gap-2 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
            <h4 class="fs-18 fw-semibold m-0">@yield('title')</h4>
        </div>
        <div>
            @if(in_array($result->status, [23, 25, 28, 29]))
            <a href="#" class="btn btn-sm fs-7 btn-primary button-invoices-edit" data-invoice-id="{{$result->id}}"><i class="fa fa-edit"></i> Editar Fatura</a>
            @endif

            @if(in_array($result->status, [23, 25, 28, 29]))
            <button type="button" class="btn btn-sm fs-7 btn-success button-invoices-confirm-payment"
                data-invoice-id="{{$result->id}}">
                <i class="fa fa-check"></i> Confirmar Pagamento
            </button>
            @endif

            @if(in_array($result->status, [23, 25, 28, 29]))
            <button type="button" class="btn btn-sm fs-7 btn-info button-invoices-send-reminder"
                data-invoice-id="{{$result->id}}">
                <i class="fa fa-comment"></i> Enviar Lembrete
            </button>
            @endif

            @if(in_array($result->status, [23, 28, 29]))
            <button type="button" class="btn btn-sm fs-7 btn-danger button-invoices-cancel"
                data-invoice-id="{{$result->id}}">
                <i class="fa fa-times"></i> Cancelar
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Invoice Detail-->
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="mt-3">
                                <h1 class="h3 mb-2"><b>{{ $result->user->name }}</b></h1>
                                <p class="mb-0 text-muted">{{$result->user->email}}</p>
                            </div>
                        </div><!-- end col -->
                        <div class="col-sm-6 offset-sm-2">
                            <div class="mt-3 float-sm-end">
                                <p class="fs-6"><strong>Gerada em: </strong> {{ $result->created_at }}</p>
                                <p class="fs-6"><strong>Vence em: </strong> {{ $result->due_at }}</p>
                                <p class="fs-6"><strong>Paga em: </strong> {{ $result->paid_at }}</p>
                                @php
                                $statusColors = [
                                23 => 'bg-warning', // Pendente (amarelo)
                                24 => 'bg-success', // Pago (verde)
                                25 => 'bg-danger', // Não Pago (vermelho)
                                26 => 'bg-secondary',// Cancelado (cinza)
                                27 => 'bg-dark', // Estornado (preto)
                                28 => 'bg-info', // Aguardando Confirmação (azul claro)
                                29 => 'bg-primary', // Parcialmente Pago (azul)
                                ];
                                $badgeClass = $statusColors[$result->status] ?? 'bg-secondary'; // Caso algum status novo apareça
                                @endphp

                                <p class="fs-6">
                                    <strong>Status: </strong>
                                    <span class="badge {{ $badgeClass }} float-end">{{ $result->status_name }}</span>
                                </p>
                            </div>
                        </div><!-- end col -->
                    </div>
                    <!-- end row -->

                    @if(isset($result->user->address))
                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <h6 class="fs-14">Endereço de Cobrança</h6>
                            <address>
                                {{ $result->user->address }}<br>
                                {{ $result->user->city }}, {{ $result->user->state }}<br>
                                {{ $result->user->zip_code }}<br>
                                <abbr title="Phone">P:</abbr> {{ $result->user->phone }}
                            </address>
                        </div>
                        <div class="col-6">
                            <h6 class="fs-14">Endereço de Entrega</h6>
                            <address>
                                {{ $result->user->address }}<br>
                                {{ $result->user->city }}, {{ $result->user->state }}<br>
                                {{ $result->user->zip_code }}<br>
                                <abbr title="Phone">P:</abbr> {{ $result->user->phone }}
                            </address>
                        </div>
                    </div>
                    <!-- end row -->
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-centered table-hover table-bordered border-light fs-7">
                                    <thead class="border-top border-bottom bg-light-subtle border-light">
                                        <tr>
                                            <th>Descrição</th>
                                            <th>Quantidade</th>
                                            <th>Preço Unitário</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($result->items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }}x</td>
                                            <td>R$ {{ $item->price_unit }}</td>
                                            <td>R$ {{ $item->price_total }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="border-top border-light">
                                            <td colspan="3" class="text-end"></td>
                                            <td class="fw-bold">R$ {{ $result->total }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->
                </div>
            </div>
        </div>
    </div>

    @if(in_array($result->status, [23, 25, 28, 29]))
    <div class="row">
        <div class="col-12">

            <form id="form-request-generate-payment">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <label for="payment-method" class="form-label">Meio de Pagamento</label>
                                    @if(isset($settingsMercadoPago) && isset($settingsMercadoPago['status_pix']) && $settingsMercadoPago['status_pix'] == 1)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input cursor-pointer" type="radio" name="payment_method" id="payment-method-pix" value="pix">
                                        <label class="form-check-label cursor-pointer" for="payment-method-pix">
                                            PIX
                                        </label>
                                    </div>
                                    @endif

                                    @if(isset($settingsMercadoPago) && isset($settingsMercadoPago['status_boleto']) && $settingsMercadoPago['status_boleto'] == 1)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input cursor-pointer" type="radio" name="payment_method" id="payment-method-boleto" value="boleto">
                                        <label class="form-check-label cursor-pointer" for="payment-method-boleto">
                                            Boleto Bancário
                                        </label>
                                    </div>
                                    @endif

                                    @if(isset($settingsMercadoPago) && isset($settingsMercadoPago['status_credit_card']) && $settingsMercadoPago['status_credit_card'] == 1)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input cursor-pointer" type="radio" name="payment_method" id="payment-method-credit-card" value="credit_card">
                                        <label class="form-check-label cursor-pointer" for="payment-method-credit-card">
                                            Cartão de Crédito
                                        </label>
                                    </div>
                                    @endif

                                    <div class="">
                                        <button type="button" class="btn btn-success button-payment-principal button-invoices-generate-payment" data-invoice-id="{{$result->id}}">Pagar Agora</button>
                                    </div>

                                    <div>
                                        @if(isset($result->latestWebhook->response_json['status']) && $result->latestWebhook->response_json['status'] == 'rejected')
                                        <div class="alert alert-danger mt-1 p-1 fs-7">{{$result->latestWebhook->response_json['message']}}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-7">
                        @if(isset($result->latestWebhook->response_json['qr_code_base64']))
                        <div id="pix-info" class="card">
                            <div class="card-body p-2">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <img src="data:image/png;base64,{{ $result->latestWebhook->response_json['qr_code_base64'] }}" alt="QR Code" class="img-fluid border rounded mb-2" style="max-width:140px;">
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <div class="qrcode-copy alert alert-info p-1 mb-1 cursor-pointer">
                                            <p class="mb-0 fs-8">{{$result->latestWebhook->response_json['qr_code']}}</p>
                                        </div>
                                        <p class="fs-8 mb-0">Clique para copiar o código PIX</p>
                                        <div id="qrcode-success"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div id="credit-card-info" class="d-none">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="d-flex flex-wrap flex-column flex-md-row gap-2">
                                        <div class="col mb-1">
                                            <label for="name-holder" class="form-label fs-7">Nome Completo</label>
                                            <input type="text" class="form-control" id="name-holder" name="name_holder" placeholder="João da Silva" value="{{ $result->user->name}}">
                                        </div>
                                        <div class="mb-1">
                                            <label for="document-holder" class="form-label fs-7">Documeno do Titular</label>
                                            <input type="text" class="form-control mask-doc" id="document-holder" name="document_holder" placeholder="000.000.000-00" value="{{ $result->user->document ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap flex-column flex-md-row justify-content-between gap-2">
                                        <div class="col mb-1">
                                            <label for="card_number" class="form-label fs-7">Número do Cartão</label>
                                            <div class="input-group">
                                                <span class="input-group-text p-1" id="card_brand_image_container">
                                                    <img id="card_brand_image" src="" alt="" style="height: 34px; display: none;">
                                                </span>
                                                <input type="text" class="form-control mask-card-number" id="card_number" name="card_number" placeholder="0000 0000 0000 0000">
                                                <input type="hidden" name="brand_card" id="brand_card" value="">
                                                <span class="input-group-text">CVC</span>
                                                <input type="text" class="form-control" id="security_code" name="security_code" placeholder="123" style="max-width:66px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap flex-column flex-md-row gap-2">
                                        <div class="col d-flex flex-wrap flex-column flex-md-row gap-1">
                                            <div class="mb-1">
                                                <label for="expiration_month" class="form-label fs-7">Mês</label>
                                                <select class="form-select" id="expiration_month" name="expiration_month">
                                                    @for ($i = 1; $i <= 12; $i++)
                                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $i == 1 ? 'selected' : '' }}>
                                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                                        </option>
                                                        @endfor
                                                </select>
                                            </div>

                                            <div class="mb-1">
                                                <label for="expiration_year" class="form-label fs-7">Ano</label>
                                                <select class="form-select" id="expiration_year" name="expiration_year">
                                                    @php
                                                    $currentYear = date('Y');
                                                    @endphp
                                                    @for ($i = $currentYear; $i <= $currentYear + 15; $i++)
                                                        <option value="{{ $i }}" {{ $i == 2025 ? 'selected' : '' }}>
                                                        {{ $i }}
                                                        </option>
                                                        @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col mb-1">
                                            <label for="installments" class="form-label fs-7">Quantas parcelas?</label>
                                            <select name="installments" id="installments" class="form-select"></select>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                                        <div class="">
                                            <button type="button" class="btn btn-success button-invoices-generate-payment" data-invoice-id="{{$result->id}}">Pagar Agora</button>
                                        </div>
                                        <div>
                                            <p class="mb-1 text-muted"><img src="{{ asset('/galerias/icons/payments/mercadopago.png') }}" alt="Mercado Pago" class="img-fluid" width="100"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@section('pageMODAL')
<div class="offcanvas offcanvas-end" tabindex="-1" id="modalInvoices" aria-labelledby="modalInvoicesLabel">
    <div class="offcanvas-header">
        <h5 id="modalInvoicesLabel"></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div> <!-- end offcanvas-header-->

    <div class="offcanvas-body" id="modal-content-invoices">
    </div> <!-- end offcanvas-body-->
</div> <!-- end offcanvas-->
@endsection

@section('pageCSS')
@endsection

@section('pageJS')
<script>
    // Copy QR Code
    $(document).on('click', '.qrcode-copy', function(e) {
        var qrCodeText = this.querySelector('p').innerText;
        navigator.clipboard.writeText(qrCodeText).then(function() {
            sendNotification("Sucesso!", "Código PIX copiado com sucesso!", "top-right", "rgba(0,0,0,0.05)", "success");
        }).catch(function(error) {
            Swal.fire({
                text: 'Erro ao copiar o código PIX.',
                icon: 'error',
                showClass: {
                    popup: 'animate_animated animate_wobble'
                }
            });
        });
    });

    // Payment Method
    $(document).on('change', 'input[name="payment_method"]', function(e) {
        if (this.value === 'pix') {
            $('#credit-card-info').addClass('d-none');
            $('#pix-info').removeClass('d-none');
            $('.button-payment-principal').show();
        } else if (this.value === 'credit_card') {
            $('#credit-card-info').removeClass('d-none');
            $('#pix-info').addClass('d-none');
            $('.button-payment-principal').hide();
        }
    });
</script>

<script>
    document.getElementById('card_number').addEventListener('input', function() {
        let cardNumber = this.value.replace(/\D/g, '').substring(0, 6); // Pega apenas os 6 primeiros números do cartão
        let invoiceId = "{{ $result->id }}"; // Pegue o ID da fatura do Blade

        if (cardNumber.length === 6) {
            fetch(`/admin/invoices/${invoiceId}/load-installments?card_number=${cardNumber}`)
                .then(response => response.json())
                .then(data => {
                    if (data.installments) {
                        updateInstallmentsSelect(data.installments);
                        document.getElementById('brand_card').value = data.card_brand; // Atualiza o input oculto
                        updateCardBrandImage(data.card_brand);
                    } else {
                        Swal.fire({
                            text: 'Validação: ' + data,
                            icon: 'warning',
                            showClass: {
                                popup: 'animate_animated animate_wobble'
                            }
                        });
                    }
                })
                .catch(error => console.error('Erro:', error));
        }
    });

    function updateInstallmentsSelect(installments) {
        let select = document.getElementById('installments');
        select.innerHTML = ''; // Limpa as opções anteriores

        Object.values(installments).forEach((item) => {
            let option = document.createElement('option');
            option.value = item.parcela;
            // option.textContent = `${item.parcela}x de R$ ${item.valor_parcela} (Total: R$ ${item.valor_total}, Juros: ${item.juros})`;
            option.textContent = `${item.parcela}x de R$ ${item.valor_parcela} (${item.juros} juros)`;
            select.appendChild(option);
        });
    }

    function updateCardBrandImage(imageUrl) {
        let imgElement = document.getElementById('card_brand_image');

        if (imageUrl) {
            imgElement.src = `{{ asset('/galerias/icons/payments/${imageUrl}.png') }}`;
            imgElement.style.display = "block"; // Exibe a imagem
        } else {
            imgElement.style.display = "none"; // Esconde se não houver imagem
        }
    }
</script>

@if($result->status == 23 && isset($result->latestWebhook))
<script>
    function checkPaymentStatus() {
        $.ajax({
            url: "{{ route('webhook.invoices.checkPaymentStatus', $result->id) }}",
            method: "GET",
            success: function(response) {
                if (response.status === "paid") {
                    let timerInterval
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Pagamento confirmado com sucesso!',
                        html: 'Aguarde alguns segundos...',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    }).then((result) => {
                        /* Read more about handling dismissals below */
                        if (result.dismiss === Swal.DismissReason.timer) {
                            location.reload();
                        }
                    });
                }
            },
            error: function() {
                console.error("Erro ao verificar o status do pagamento.");
            }
        });
    }

    $(document).ready(function() {
        setInterval(checkPaymentStatus, 30000); //30s
    });
</script>
@endif

<script>
    // Button Generate Payment
    $(document).on('click', '.button-invoices-generate-payment', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        let button = $(this);
        let invoice_id = button.data('invoice-id');

        var url = `{{ url('/admin/invoices/${invoice_id}/generate-payment') }}`;

        var form = $('#form-request-generate-payment')[0];
        var formData = new FormData(form);

        $.ajax({
            url: url,
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            method: 'POST',
            beforeSend: function() {
                //disable the submit button
                button.attr("disabled", true);
                button.append('<i class="fa fa-spinner fa-spin ml-3"></i>');
            },
            complete: function() {
                button.prop("disabled", false);
                button.find('.fa-spinner').addClass('d-none');
            },
            success: function(data) {
                if (data.payment_method == 'pix' || data.payment_method == 'boleto') {
                    location.reload();
                } else {
                    Swal.fire({
                        text: data.message,
                        icon: data.status != 'rejected' ? 'success' : 'error',
                        showClass: {
                            popup: 'animate_animated animate_wobble'
                        }
                    }).then(function() {
                        if (data.status != 'rejected') {
                            location.reload()
                        }
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    Swal.fire({
                        text: 'Validação: ' + xhr.responseJSON,
                        icon: 'warning',
                        showClass: {
                            popup: 'animate_animated animate_wobble'
                        }
                    });
                } else {
                    Swal.fire({
                        text: 'Erro Interno: ' + xhr.responseJSON,
                        icon: 'error',
                        showClass: {
                            popup: 'animate_animated animate_wobble'
                        }
                    });
                }
            }
        });
    });
</script>

@include('admin.pages.invoices.scripts')
@endsection