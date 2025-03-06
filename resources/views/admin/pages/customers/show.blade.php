@extends('admin.base')

@section('title', $customer->name)

@section('content')
<div class="container">
    <div class="row py-2">
        <div class="col-12 col-md-4 col-lg-4">
            <div class="card custom-card overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-sm-flex align-items-top border-bottom pb-2 mb-2">
                        <div>
                            <img src="{{ asset('/galerias/avatares/innsystem.png') }}" alt="{{$customer->name}}" class="avatar-sm rounded-circle me-1">
                        </div>
                        <div class="flex-fill main-profile-info">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="h4 fw-semibold mb-1 text-fixed-white">{{$customer->name}}</h4>
                            </div>
                            <p class="mb-1 text-muted text-fixed-white ">Chief Executive Officer (C.E.O)</p>
                            <p class="fs-12 text-fixed-white mb-2 ">
                                <span class="me-1"><i class="ri-building-line me-1 align-middle"></i>Georgia</span>
                                <span><i class="ri-map-pin-line me-1 align-middle"></i>Washington D.C</span>
                            </p>
                            <div class="d-flex mb-0">
                                <div class="me-4">
                                    <p class="fw-bold fs-5 text-fixed-white text-shadow mb-0">113</p>
                                    <p class="mb-0 fs-11  text-fixed-white">Projects</p>
                                </div>
                                <div class="me-4">
                                    <p class="fw-bold fs-5 text-fixed-white text-shadow mb-0">12.2k</p>
                                    <p class="mb-0 fs-11  text-fixed-white">Followers</p>
                                </div>
                                <div class="me-4">
                                    <p class="fw-bold fs-5 text-fixed-white text-shadow mb-0">128</p>
                                    <p class="mb-0 fs-11  text-fixed-white">Following</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="mb-2">
                            <p class="fs-15 mb-2 fw-semibold">Professional Bio :</p>
                            <p class="fs-12 text-muted  mb-0"> I am <b class="text-default">Sonya Taylor,</b> here by conclude that,i am the founder and managing director of the prestigeous company name laugh at all and acts as the cheif executieve officer of the company. </p>
                        </div>
                        <div class="mb-0">
                            <p class="fs-15 mb-2 fw-semibold">Links :</p>
                            <div class="mb-0">
                                <p class="mb-1"> <a href="https://www.spruko.com/" class="text-primary"><u>https://www.spruko.com/</u></a> </p>
                                <p class="mb-0"> <a href="https://themeforest.net/user/spruko/portfolio" class="text-primary"><u>https://themeforest.net/user/spruko/portfolio</u></a> </p>
                            </div>
                        </div>
                    </div>
                    <div class="border-bottom pb-2 mb-2">
                        <p class="fs-15 mb-2 me-4 fw-semibold">Contact Information :</p>
                        <div class="text-muted">
                            <p class="mb-2"> <span class="avatar avatar-sm avatar-rounded me-2 bg-light text-muted"> <i class="ri-mail-line align-middle fs-14"></i> </span> sonyataylor2531@gmail.com </p>
                            <p class="mb-2"> <span class="avatar avatar-sm avatar-rounded me-2 bg-light text-muted"> <i class="ri-phone-line align-middle fs-14"></i> </span> +(555) 555-1234 </p>
                            <p class="mb-0"> <span class="avatar avatar-sm avatar-rounded me-2 bg-light text-muted"> <i class="ri-map-pin-line align-middle fs-14"></i> </span> MIG-1-11, Monroe Street, Georgetown, Washington D.C, USA,20071 </p>
                        </div>
                    </div>
                    <div class="border-bottom pb-2 mb-2">
                        <p class="fs-15 mb-2 me-4 fw-semibold">Skills :</p>
                        <div> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Cloud computing</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Data analysis</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">DevOps</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Machine learning</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Programming</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Security</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Python</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">JavaScript</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Ruby</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">PowerShell</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">Statistics</span> </a> <a href="javascript:void(0);"> <span class="badge bg-light text-muted m-1">SQL</span> </a> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-8 col-lg-8">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body p-2">
                            <div class="p-1 border-bottom pb-2 mb-2 d-flex align-items-center justify-content-between">
                                <ul class="nav nav-tabs mb-0 tab-style-6 justify-content-start" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-tab-pane" type="button" role="tab" aria-controls="activity-tab-pane" aria-selected="true">
                                            <i class="ri-file-line me-1 align-middle d-inline-block"></i>Faturas
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="p-0">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane show active fade p-0 border-0" id="activity-tab-pane" role="tabpanel" aria-labelledby="activity-tab" tabindex="0">
                                        <!-- content tab-pane -->
                                        @include('components.invoices_table', ['invoices' => $customerInvoices, 'hideClientColumn' => true])
                                        <!-- content tab-pane -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
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

@include('admin.pages.invoices.scripts')
@endsection