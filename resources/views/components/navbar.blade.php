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
    <div class="container-fluid px-4 h-100 d-flex align-items-stretch justify-content-between">

        {{-- ========== LEFT: LOGO + TEXT ========== --}}
        <div class="nav-left d-flex align-items-center gap-3">

            {{-- โลโก้ซ้าย เล็กลง คลีน ไม่มีพื้นหลังเสริม --}}
            <a href="{{ url('/') }}" class="d-flex align-items-center" data-no-loader>
                <img src="{{ $logo }}" alt="Logo" class="logo-img">
            </a>

            {{-- Block ตัวหนังสือจัด layout ให้บาลานซ์ --}}
            <div class="nav-left-text d-none d-lg-flex flex-column justify-content-center">

                {{-- แถวบน: ชื่อระบบ + หน่วยงาน IT แยกคอลัมน์เบา ๆ --}}
                <div class="brand-top-row d-flex align-items-center gap-3">
                    <span class="brand-system ff-sarabun">
                        {{ $systemName }}
                    </span>
                    <span class="brand-app ff-sarabun d-none d-xl-inline">
                        {{ $appName }}
                    </span>
                </div>

                {{-- แถวล่าง: ชื่อโรงพยาบาล EN + TH --}}
                <div class="brand-hospital-block">
                    <div class="brand-hospital-en ff-sarabun">
                        {{ $hospitalNameEn }}
                    </div>
                    <div class="brand-hospital-th ff-sarabun">
                        {{ $hospitalName }}
                    </div>
                </div>
            </div>

            {{-- mobile text --}}
            <div class="nav-left-text-mobile d-flex d-lg-none flex-column lh-1">
                <span class="ff-sarabun text-amber-200 fw-semibold" style="font-size: 0.8rem;">
                    {{ $systemName }}
                </span>
                <span class="ff-sarabun text-white fw-semibold" style="font-size: 0.9rem;">
                    {{ $hospitalNameEn }}
                </span>
                <span class="ff-sarabun text-white-50" style="font-size: 0.75rem;">
                    {{ $hospitalName }}
                </span>
            </div>
        </div>

        {{-- ========== RIGHT: PROFILE / LOGIN + TOGGLER ========== --}}
        <div class="nav-right d-flex align-items-center gap-3">

            {{-- Desktop profile/login --}}
            <div class="d-none d-lg-block">
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
                       class="btn btn-outline-light btn-sm ff-sarabun px-3">
                        เข้าสู่ระบบ
                    </a>
                @endguest
            </div>

            {{-- Mobile toggler --}}
            <button class="navbar-toggler custom-toggler d-lg-none" type="button"
                    data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

    </div>
</nav>

{{-- ========== MOBILE MENU ========== --}}
<div class="collapse navbar-collapse bg-navy d-lg-none" id="mobileMenu">
    <div class="p-3 border-top border-slate-300-ppk">
        <div class="text-amber-200 mb-1 small ff-sarabun fw-semibold">
            {{ $systemName }}
        </div>
        <div class="text-white ff-sarabun mb-3">
            {{ $hospitalName }}
        </div>

        @auth
            <div class="mobile-profile-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                         width="32" height="32" class="rounded-circle">
                    <div class="text-white ff-sarabun small">
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-white-50">{{ $user->email }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}"
                   class="btn btn-outline-light btn-sm w-100 ff-sarabun mb-2">
                    โปรไฟล์ของฉัน
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger w-100 btn-sm ff-sarabun">ออกจากระบบ</button>
                </form>
            </div>
        @endauth

        @guest
            <a href="{{ route('login') }}"
               class="btn btn-light w-100 btn-sm ff-sarabun">
                เข้าสู่ระบบ
            </a>
        @endguest
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

:root {
    --nav-height: 80px;
    --nav-bg: #0F2D5C; /* สีเดิมของระบบ */
}

.ff-sarabun { font-family: 'Sarabun', sans-serif !important; }

.navbar-ppk {
    height: var(--nav-height);
    background-color: var(--nav-bg); /* คงสีเดิม */
    padding: 0;
    z-index: 1200;
    border-bottom: 1px solid rgba(226,232,240,0.35);
}

/* layout ซ้าย-ขวาให้เต็มความสูง */
.nav-left, .nav-right {
    height: 100%;
}

/* logo เล็กลง คลีน */
.logo-img {
    height: 40px;
    width: auto;
    object-fit: contain;
}

/* block ข้อความฝั่งซ้าย + เส้นแนวตั้งบาง ๆ */
.nav-left-text {
    padding-left: 12px;
    border-left: 1px solid rgba(226,232,240,0.35);
}

/* แถวบน: system + app */
.brand-top-row {
    margin-bottom: 2px;
}

/* system name – เน้นเป็น label */
.brand-system {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: #FACC15;
    font-weight: 600;
    white-space: nowrap;
}

/* app name */
.brand-app {
    font-size: 0.7rem;
    color: rgba(241,245,249,0.9);
    white-space: nowrap;
}

/* hospital block + เส้นบางด้านบนให้ฟีล grid */
.brand-hospital-block {
    padding-top: 3px;
}
.brand-hospital-block::before {
    content: "";
    display: block;
    width: 72px;
    height: 1px;
    margin-bottom: 4px;
    background-color: rgba(226,232,240,0.5);
}

/* hospital EN/TH */
.brand-hospital-en {
    font-size: 1.02rem;
    font-weight: 700;
    color: #F9FAFB;
    letter-spacing: 0.045em;
}
.brand-hospital-th {
    font-size: 0.86rem;
    color: rgba(241,245,249,0.95);
}

/* Avatar / Profile */
.avatar-img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(226,232,240,0.85);
}
.profile-dropdown-menu {
    min-width: 260px;
    border-radius: 10px;
    border: 1px solid rgba(209,213,219,0.6);
}

/* Mobile */
.bg-navy {
    background-color: var(--nav-bg);
}
.border-slate-300-ppk {
    border-color: rgba(226,232,240,0.7) !important;
}

.mobile-profile-card {
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid rgba(226,232,240,0.7);
}

/* Toggler */
.custom-toggler {
    border-color: rgba(248,250,252,0.85);
}
.custom-toggler .navbar-toggler-icon {
    background-image:
        linear-gradient(to bottom,
            rgba(248,250,252,0.95) 0,
            rgba(248,250,252,0.95) 40%,
            transparent 40%, transparent 60%,
            rgba(248,250,252,0.95) 60%,
            rgba(248,250,252,0.95) 100%);
}

/* Responsive */
@media (max-width: 991px) {
    .navbar-ppk {
        height: 64px;
        padding-inline: 1rem;
    }
    .nav-left-text {
        border-left: none;
        padding-left: 0;
    }
    .logo-img {
        height: 36px;
    }
}
</style>
