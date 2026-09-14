<header class="navbar navbar-expand-md sticky-top d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('img/logo.jpg') }}" alt="{{ config('app.name') }}" class="navbar-brand-image" style="height: 38px; width: auto; object-fit: contain;">
            </a>
        </div>
        <div class="navbar-nav flex-row order-md-last align-items-center gap-3">
            @if(Auth::check())
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex align-items-center p-1 text-reset" data-bs-toggle="dropdown" aria-label="Menu Pengguna">
                        <span class="avatar avatar-sm rounded-circle text-uppercase fw-bold bg-primary-subtle text-primary border-primary">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </span>
                        <div class="d-none d-md-block ps-2 text-start">
                            <div class="fw-semibold text-dark">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">{{ Auth::user()->roles->first()->name ?? 'User' }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-chevron-down ms-1 text-muted d-none d-md-inline-block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 9l6 6l6 -6" /></svg>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-lg border-0 mt-2">
                        <div class="dropdown-header">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="dropdown-divider"></div>
                        @role('Admin')
                            <a href="{{ route('setting') }}" class="dropdown-item d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                Pengaturan Sistem
                            </a>
                        @endrole
                        <a href="{{ route('logout') }}" class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                            Keluar (Logout)
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>