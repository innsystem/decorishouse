<li class="side-nav-title">Menu</li>

<li class="side-nav-item">
    <a href="{{ route('site.index') }}" class="side-nav-link" target="_Blank">
        <i class="ri-home-2-line"></i>
        <span> Meu Site </span>
    </a>
</li>

@if (auth()->user()->hasPermission('admin.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.index') }}" class="side-nav-link">
        <i class="ri-home-4-line"></i>
        <span> Início </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.commander') || auth()->user()->hasPermission('admin.statuses.index') || auth()->user()->hasPermission('admin.permissions.index') || auth()->user()->hasPermission('admin.user_groups.index'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarDeveloper" aria-expanded="false" aria-controls="sidebarDeveloper" class="side-nav-link">
        <i class="ri-settings-5-line"></i>
        <span> Developer </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="sidebarDeveloper">
        <ul class="side-nav-second-level">
            @if (auth()->user()->hasPermission('admin.commander'))
            <li><a href="{{ route('admin.commander') }}">CommanderCRUD</a></li>
            @endif
            @if (auth()->user()->hasPermission('admin.statuses.index'))
            <li><a href="{{ route('admin.statuses.index') }}">Situações de Status</a></li>
            @endif
            @if (auth()->user()->hasPermission('admin.permissions.index'))
            <li><a href="{{ route('admin.permissions.index') }}">Permissões</a></li>
            @endif
            @if (auth()->user()->hasPermission('admin.user_groups.index'))
            <li><a href="{{ route('admin.user_groups.index') }}">Grupo de Usuários</a></li>
            @endif
        </ul>
    </div>
</li>
@endif

@if (auth()->user()->hasPermission('admin.settings') || auth()->user()->hasPermission('admin.users.index') || auth()->user()->hasPermission('admin.integrations.index'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="false" aria-controls="sidebarSettings" class="side-nav-link">
        <i class="ri-settings-3-line"></i>
        <span> Configurações </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="sidebarSettings">
        <ul class="side-nav-second-level">
            @if (auth()->user()->hasPermission('admin.settings'))
            <li><a href="{{ route('admin.settings') }}">Informações Gerais</a></li>
            @endif
            @if (auth()->user()->hasPermission('admin.users.index'))
            <li><a href="{{ route('admin.users.index') }}">Administradores</a></li>
            @endif
            @if (auth()->user()->hasPermission('admin.integrations.index'))
            <li><a href="{{ route('admin.integrations.index') }}">Integrações</a></li>
            @endif
        </ul>
    </div>
</li>
@endif

<li class="side-nav-title">Recursos</li>

@if (auth()->user()->hasPermission('admin.customers.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.customers.index') }}" class="side-nav-link">
        <i class="ri-group-line"></i>
        <span> Clientes </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.pages.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.pages.index') }}" class="side-nav-link">
        <i class="ri-pages-line"></i>
        <span> Páginas </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.services.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.services.index') }}" class="side-nav-link">
        <i class="ri-function-line"></i>
        <span> Serviços </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.portfolios.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.portfolios.index') }}" class="side-nav-link">
        <i class="ri-image-fill"></i>
        <span> Portfólios </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.testimonials.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.testimonials.index') }}" class="side-nav-link">
        <i class="ri-chat-heart-fill"></i>
        <span> Depoimentos </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.sliders.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.sliders.index') }}" class="side-nav-link">
        <i class="ri-image-line"></i>
        <span> Slider </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.invoices.index') || auth()->user()->hasPermission('admin.transactions.index'))
<li class="side-nav-item">
    <a data-bs-toggle="collapse" href="#sidebarFinances" aria-expanded="false" aria-controls="sidebarFinances" class="side-nav-link">
        <i class="ri-exchange-dollar-line"></i>
        <span> Finanças </span>
        <span class="menu-arrow"></span>
    </a>
    <div class="collapse" id="sidebarFinances">
        <ul class="side-nav-second-level">
            @if (auth()->user()->hasPermission('admin.invoices.index'))
            <li><a href="{{ route('admin.invoices.index') }}"><i class="ri-file-line"></i> Faturas</a></li>
            @endif

            @if (auth()->user()->hasPermission('admin.transactions.index'))
            <li><a href="{{ route('admin.transactions.index') }}"><i class="ri-price-tag-3-line"></i> Fluxo de Caixa</a></li>
            @endif
        </ul>
    </div>
</li>
@endif

@if (auth()->user()->hasPermission('admin.categories.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.categories.index') }}" class="side-nav-link">
        <i class="ri-bar-chart-horizontal-fill"></i>
        <span> Categorias </span>
    </a>
</li>
@endif

@if (auth()->user()->hasPermission('admin.products.index'))
<li class="side-nav-item">
    <a href="{{ route('admin.products.index') }}" class="side-nav-link">
        <i class="ri-price-tag-2-line"></i>
        <span> Produtos </span>
    </a>
</li>
@endif


<li class="side-nav-item">
    <a href="#" class="side-nav-link" id="suggestProductsBtn">
        <i class="ri-price-tag-line"></i>
        <span> Gerar Roteiro </span>
    </a>
</li>
<li class="side-nav-item">
    <a href="#" class="side-nav-link" id="randomProductsBtn">
        <i class="ri-image-line"></i>
        <span> Gerar Imagem </span>
    </a>
</li>