{{-- resources/views/components/navbar.blade.php --}}
@props([
    'logo'         => asset('images/logoppk.png'),
    'bannerText'   => null,
    'bannerAction' => null,
    'bannerLabel'  => null,
    'showLogout'   => Auth::check(),
])

@php
    $user = Auth::user();
    $breadcrumbs = \App\Support\Breadcrumb::generate();
@endphp

<nav class="navbar navbar-expand-lg navbar-pinwheel shadow-sm fixed-top">
    <div class="container-fluid px-4 h-100 d-flex align-items-center">

        {{-- LEFT : SYSTEM TITLE --}}
        <div class="nav-left d-none d-md-flex align-items-center gap-2">
            <div class="brand-en nav-system-title">
                Asset Repair Management System
            </div>
        </div>

        {{-- CENTER : BREADCRUMB & BANNER --}}
        <div class="nav-center d-none d-md-flex align-items-center flex-grow-1 px-4">

            {{-- 1. ส่วนแสดง Breadcrumb --}}
            @if(empty($bannerText) && count($breadcrumbs) > 0)
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom m-0 ff-sarabun align-items-center">
                        @foreach($breadcrumbs as $item)
                            <li class="breadcrumb-item {{ $item['active'] ? 'active' : '' }}">
                                @if($item['url'] && !$item['active'])
                                    <a href="{{ $item['url'] }}" class="text-decoration-none nav-breadcrumb-link">
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    <span class="nav-breadcrumb-active">
                                        {{ $item['label'] }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            {{-- 2. ส่วน Banner --}}
            @if($bannerText)
                <span class="nav-banner-text me-3">{{ $bannerText }}</span>
                @if($bannerAction && $bannerLabel)
                    <a href="{{ $bannerAction }}" class="btn btn-sm nav-banner-btn">
                        {{ $bannerLabel }}
                    </a>
                @endif
            @endif
        </div>

        {{-- RIGHT : PROFILE --}}
        <div class="nav-right ms-auto d-flex align-items-center gap-3">

            {{-- Desktop --}}
            <div class="d-none d-md-block">
                @auth
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0"
                               href="#" id="profileDropdown"
                               role="button" data-bs-toggle="dropdown">
                                <span class="ff-sarabun nav-username d-none d-lg-inline">
                                    {{ $user->name }}
                                </span>
                                <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                                     class="avatar-img" alt="Avatar">
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm profile-dropdown-menu">
                                <li class="px-3 pt-2 pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                                             width="42" height="42" class="rounded-circle">
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
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="px-3 pb-2">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm w-100 ff-sarabun">
                                            <i class="bi bi-box-arrow-right me-1"></i> ออกจากระบบ
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endauth
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm ff-sarabun px-3">
                        เข้าสู่ระบบ
                    </a>
                @endguest
            </div>

            {{-- Mobile --}}
            <div class="d-md-none">
                @auth
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                        <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}" class="avatar-img" alt="Avatar">
                    </button>
                @endauth
                @guest
                    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                @endguest
            </div>
        </div>
    </div>
</nav>

{{-- MOBILE MENU --}}
<div class="collapse navbar-collapse bg-white border-top d-md-none" id="mobileMenu">
    <div class="p-3">
        @if(count($breadcrumbs) > 0)
            <div class="mb-3 pb-2 border-bottom">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 ff-sarabun small">
                        @foreach($breadcrumbs as $item)
                            <li class="breadcrumb-item {{ $item['active'] ? 'active' : '' }}">
                                @if($item['url'] && !$item['active'])
                                    <a href="{{ $item['url'] }}" class="text-decoration-none text-muted">{{ $item['label'] }}</a>
                                @else
                                    <span class="fw-semibold">{{ $item['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        @endif

        @auth
            <div class="mobile-profile-card mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}" width="32" height="32" class="rounded-circle" alt="Avatar">
                    <div class="ff-sarabun small">
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted">{{ $user->email }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary btn-sm w-100 ff-sarabun mb-2">
                    โปรไฟล์ของฉัน
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger w-100 btn-sm ff-sarabun">ออกจากระบบ</button>
                </form>
            </div>
        @endauth
        @guest
            <a href="{{ route('login') }}" class="btn btn-primary w-100 btn-sm ff-sarabun">เข้าสู่ระบบ</a>
        @endguest
    </div>
</div>

{{-- ==================  CSS ================== --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

:root{
  --nav-height: 80px;
  --ppk-blue: #0F2D5C;
  --ppk-blue-2: #133A73;
  --ppk-border: #e2e8f0;
  --ppk-text: #0f172a;
  --ppk-muted: #64748b;
  --ppk-soft: rgba(15,45,92,.08);
}

.ff-sarabun{ font-family: 'Sarabun', sans-serif !important; }
.brand-en{ font-family: 'Sarabun', sans-serif; }

/* ===== NAVBAR LAYOUT ===== */
.navbar-pinwheel{
  height: var(--nav-height);
  background: #ffffff;
  padding: 0;
  border-bottom: 1px solid var(--ppk-border);
  z-index: 1200;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
}

.nav-system-title{
  font-size: .85rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: var(--ppk-text);
  white-space: nowrap;
}

/* --- BREADCRUMB GLOW EFFECT (NEW) --- */
.breadcrumb-custom {
    --bs-breadcrumb-divider: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%2394a3b8'/%3E%3C/svg%3E");
    --bs-breadcrumb-item-padding-x: 0.6rem;
}

.nav-breadcrumb-link {
    color: var(--ppk-muted);
    font-weight: 500;
    font-size: 0.9rem;
    padding: 2px 0; /* ตัด padding แนวนอนออก เพราะไม่มีกรอบแล้ว */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Animation นุ่มๆ */
    position: relative;
}

.nav-breadcrumb-link:hover {
    color: var(--ppk-blue); /* เปลี่ยนเป็นสีน้ำเงิน */
    text-decoration: none !important;
    background-color: transparent; /* เอาพื้นหลังออก */

    /* สร้าง Effect แสงฟุ้ง (Glow) */
    text-shadow: 0 0 12px rgba(15, 45, 92, 0.25);

    /* ลอยขึ้นนิดนึงให้ดูมีมิติ */
    transform: translateY(-1px);
}

/* Active Item */
.nav-breadcrumb-active {
    color: var(--ppk-blue);
    font-size: 0.9rem;
    font-weight: 700;
}

/* Banner */
.nav-center .nav-banner-text{ font-size: .9rem; color: var(--ppk-muted); }
.nav-banner-btn{
  border-radius: 999px;
  padding-inline: 1rem;
  font-size: .8rem;
  background-color: var(--ppk-blue);
  border-color: var(--ppk-blue);
  color: #ffffff;
}
.nav-banner-btn:hover{
  background-color: var(--ppk-blue-2);
  border-color: var(--ppk-blue-2);
}

/* Profile Avatar */
.avatar-img{
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f1f5f9;
  transition: border-color 0.2s;
}
.nav-link:hover .avatar-img { border-color: var(--ppk-soft); }
.nav-username{ font-size: 0.9rem; color: var(--ppk-text); font-weight: 600; }

/* Dropdown */
.profile-dropdown-menu{
  min-width: 260px;
  border-radius: 12px;
  border: 1px solid rgba(15,45,92,.08);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
}
.profile-dropdown-menu .dropdown-item{
  border-radius: 8px; margin-inline: 8px; width: calc(100% - 16px); padding: 8px 12px; font-size: 0.9rem;
}
.profile-dropdown-menu .dropdown-item:hover{
  background-color: var(--ppk-soft); color: var(--ppk-blue);
}

/* Mobile & Layout */
.mobile-profile-card{
  border-radius: 12px; padding: 10px 12px; border: 1px solid #e5e7eb; background-color: #ffffff;
}
.custom-toggler{ border-color: transparent; }
.custom-toggler:focus { box-shadow: none; }

@media (min-width: 1024px){
  .navbar-pinwheel{ left: 260px; width: calc(100% - 260px); }
}
</style>
