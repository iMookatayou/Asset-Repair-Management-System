{{-- resources/views/components/navbar.blade.php --}}
@props([
    'hospitalName'   => 'โรงพยาบาลพระปกเกล้าจันทบุรี',
    'hospitalNameEn' => 'PHRAPOKKLAO HOSPITAL',
    'appName'        => 'PPK Information Technology Group',
    'systemName'     => 'Asset Repair Management',
    'logo'           => asset('images/logoppk.png'),
    'showLogout'     => Auth::check(),
])

@php $user = Auth::user(); @endphp

<nav class="navbar navbar-expand-lg fixed-top navbar-ppk">
    <div class="container-fluid px-4 h-100 position-relative">

        {{-- ========== LEFT SIDE: SYSTEM NAME + HOSPITAL + LINE เข้าหาโลโก้ ========== --}}
        <div class="nav-side nav-left d-none d-lg-flex align-items-center h-100">

            <div class="nav-left-text d-flex flex-column justify-content-center lh-sm">
                {{-- บรรทัดบน: Asset Repair Management --}}
                <span class="brand-system-main ff-sarabun">
                    {{ $systemName }}
                </span>
                {{-- บรรทัดสอง: PHRAPOKKLAO HOSPITAL --}}
                <span class="brand-hospital-en ff-sarabun">
                    {{ $hospitalNameEn }}
                </span>
                {{-- บรรทัดสาม: โรงพยาบาลพระปกเกล้า --}}
                <span class="brand-hospital-th ff-sarabun">
                    {{ $hospitalName }}
                </span>
            </div>

            <div class="brand-divider mx-3"></div>

            {{-- เส้นจากข้อความซ้ายไปหาตรงกลาง --}}
            <div class="nav-line nav-line-left ms-4 flex-grow-1"></div>
        </div>

        {{-- ========== CENTER LOGO (อยู่กลางจอจริง ๆ) ========== --}}
        <div class="nav-center-logo">
            <a href="{{ url('/') }}" class="d-block" data-no-loader>
                <img src="{{ $logo }}" alt="Logo" class="logo-img">
            </a>
        </div>

        {{-- ========== RIGHT SIDE: LINE เข้าหาโลโก้ + PROFILE ========== --}}
        <div class="nav-side nav-right d-none d-lg-flex align-items-center h-100">
            {{-- เส้นจากด้านขวาเข้าหาโลโก้ --}}
            <div class="nav-line nav-line-right me-4 flex-grow-1"></div>

            @auth
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0 profile-trigger"
                           href="#"
                           id="profileDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                                 class="avatar-img" alt="Avatar">
                            <span class="d-none d-xl-inline text-white ff-sarabun fw-semibold">
                                {{ $user->name }}
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm profile-dropdown-menu"
                            aria-labelledby="profileDropdown">
                            <li class="px-3 pt-2 pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                                         width="42" height="42" class="rounded-circle" alt="Avatar">
                                    <div class="ff-sarabun">
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a href="{{ route('profile.show') }}"
                                   class="dropdown-item ff-sarabun d-flex align-items-center gap-2">
                                    <i class="bi bi-person-lines-fill"></i>
                                    โปรไฟล์ของฉัน
                                </a>
                            </li>
                            @if($showLogout)
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="px-3 pb-2">
                                    <form action="{{ route('logout') }}" method="POST" class="mb-0">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm w-100 ff-sarabun d-flex justify-content-center align-items-center gap-2">
                                            <i class="bi bi-box-arrow-right"></i>
                                            ออกจากระบบ
                                        </button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </li>
                </ul>
            @endauth

            @guest
                <a href="{{ route('login') }}"
                   class="btn btn-light btn-sm ff-sarabun rounded-pill px-3">
                    เข้าสู่ระบบ
                </a>
            @endguest
        </div>

        {{-- ========== MOBILE HEADER ========== --}}
        <div class="d-flex d-lg-none w-100 align-items-center justify-content-between">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}" data-no-loader>
                <img src="{{ $logo }}" width="40" height="40" alt="Logo">
                <div class="d-flex flex-column lh-1">
                    <span class="text-amber-300 fw-semibold" style="font-size: 0.82rem;">
                        {{ $systemName }}
                    </span>
                    <span class="text-white fw-semibold" style="font-size: 0.9rem;">
                        {{ $hospitalNameEn }}
                    </span>
                    <span class="text-white-50" style="font-size: 0.75rem;">
                        {{ $hospitalName }}
                    </span>
                </div>
            </a>
            <button class="navbar-toggler custom-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

    </div>
</nav>

{{-- ========== MOBILE MENU ========== --}}
<div class="collapse navbar-collapse bg-navy d-lg-none" id="mobileMenu">
    <div class="p-3">
        <div class="text-amber-300 mb-1 small ff-sarabun fw-semibold">
            {{ $systemName }}
        </div>
        <div class="text-white ff-sarabun mb-3">
            {{ $hospitalName }}
        </div>

        @auth
            <div class="border-top border-secondary pt-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                         width="32" class="rounded-circle">
                    <div class="text-white ff-sarabun">{{ $user->name }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger w-100 btn-sm ff-sarabun">ออกจากระบบ</button>
                </form>
            </div>
        @endauth
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

:root {
    --nav-height: 96px;      /* สูงขึ้นให้มีทรงมากขึ้น */
    --nav-bg: #0F2D5C;
}

.ff-sarabun { font-family: 'Sarabun', sans-serif !important; }

.navbar-ppk {
    height: var(--nav-height);
    background-color: var(--nav-bg);
    box-shadow: 0 6px 14px rgba(0,0,0,0.22);
    padding: 0;
    z-index: 1200;
}

/* ซ้าย/ขวา ใช้พื้นที่ครึ่งละไม่ล้ำเข้าโลโก้ */
.nav-side {
    flex: 1;
    min-width: 0;
    max-width: calc(50% - 90px);
}

/* กล่องข้อความฝั่งซ้ายให้สูงกลาง nav */
.nav-left-text span { display: block; }

/* โลโก้ตรงกลางจอ 100% */
.nav-center-logo {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 20;
}

.logo-img {
    height: 78px;
    width: auto;
}

/* Text styles */
.brand-system-main {
    font-size: 0.9rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #fbbf24;
    font-weight: 600;
}
.brand-hospital-en {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.03em;
}
.brand-hospital-th {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.9);
}

/* เส้นแบ่งเล็กระหว่าง block และเส้นยาว */
.brand-divider {
    width: 1px;
    height: 40px;
    background-color: rgba(255,255,255,0.45);
}

/* เส้นยาวจากสองฝั่งมาหาโลโก้ */
.nav-line {
    height: 2px;
    opacity: 0.7;
}
.nav-line-left {
    background: linear-gradient(to right, rgba(255,255,255,0.9), rgba(255,255,255,0.1), transparent);
}
.nav-line-right {
    background: linear-gradient(to left, rgba(255,255,255,0.9), rgba(255,255,255,0.1), transparent);
}

/* Avatar / Profile */
.avatar-img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.4);
    object-fit: cover;
}
.profile-dropdown-menu {
    min-width: 260px;
    border-radius: 10px;
    border: none;
}

/* Mobile */
.bg-navy { background-color: var(--nav-bg); }

@media (max-width: 991px) {
    .navbar-ppk {
        height: 68px;
        padding: 0 1rem;
    }
    .nav-center-logo { display: none; }
}
</style>
