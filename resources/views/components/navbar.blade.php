{{-- resources/views/components/navbar.blade.php --}}
@props([
    'logo'         => asset('images/logoppk.png'),
    'bannerText'   => null,
    'bannerAction' => null,
    'bannerLabel'  => null,
    'showLogout'   => Auth::check(),
])

@php $user = Auth::user(); @endphp

<nav class="navbar navbar-expand-lg navbar-pinwheel shadow-sm fixed-top">
    <div class="container-fluid px-4 h-100 d-flex align-items-center">

        {{-- LEFT : SYSTEM TITLE --}}
        <div class="nav-left d-none d-md-flex align-items-center gap-2">
            <div class="brand-en nav-system-title">
                Asset Repair Management System
            </div>
        </div>

        {{-- CENTER : BANNER --}}
        <div class="nav-center d-none d-md-flex align-items-center flex-grow-1 px-4">
            @if($bannerText)
                <span class="nav-banner-text me-3">{{ $bannerText }}</span>

                @if($bannerAction && $bannerLabel)
                    <a href="{{ $bannerAction }}"
                       class="btn btn-sm nav-banner-btn">
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
                    <a href="{{ route('login') }}"
                       class="btn btn-outline-primary btn-sm ff-sarabun px-3">
                        เข้าสู่ระบบ
                    </a>
                @endguest
            </div>

            {{-- Mobile --}}
            <div class="d-md-none">
                @auth
                    <button class="btn btn-link p-0"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#mobileMenu">
                        <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                             class="avatar-img" alt="Avatar">
                    </button>
                @endauth

                @guest
                    <button class="navbar-toggler custom-toggler"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#mobileMenu">
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

        @auth
            <div class="mobile-profile-card mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                         width="32" height="32" class="rounded-circle" alt="Avatar">
                    <div class="ff-sarabun small">
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted">{{ $user->email }}</div>
                    </div>
                </div>

                <a href="{{ route('profile.show') }}"
                   class="btn btn-outline-secondary btn-sm w-100 ff-sarabun mb-2">
                    โปรไฟล์ของฉัน
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger w-100 btn-sm ff-sarabun">
                        ออกจากระบบ
                    </button>
                </form>
            </div>
        @endauth

        @guest
            <a href="{{ route('login') }}"
               class="btn btn-primary w-100 btn-sm ff-sarabun">
                เข้าสู่ระบบ
            </a>
        @endguest

    </div>
</div>

{{-- ==================  CSS (ไฟล์เดียว) ================== --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

:root{
  --nav-height: 80px;

  /* โทนหลักเดียวกับ footer / brand */
  --ppk-blue: #0F2D5C;
  --ppk-blue-2: #133A73;
  --ppk-border: #e2e8f0;
  --ppk-text: #0f172a;
  --ppk-muted: #475569;
  --ppk-soft: rgba(15,45,92,.08);
}

.ff-sarabun{
  font-family: 'Sarabun', sans-serif !important;
}

.brand-en{
  font-family: 'Sarabun', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
               Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* ===== NAVBAR ===== */
.navbar-pinwheel{
  height: var(--nav-height);
  background:
    linear-gradient(180deg, rgba(15,45,92,.06) 0%, rgba(15,45,92,0) 100%),
    #ffffff;
  padding: 0;
  border-bottom: 1px solid var(--ppk-border);
  z-index: 1200;
}

/* ซ้าย: ชื่อระบบ */
.nav-system-title{
  font-size: .85rem;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  color: var(--ppk-text);
  white-space: nowrap;
}

/* กลาง: banner */
.nav-center .nav-banner-text{
  font-size: .9rem;
  color: var(--ppk-muted);
}

/* ปุ่ม banner ให้เข้ากับโทนน้ำเงิน */
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

/* โปรไฟล์ */
.avatar-img{
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(15,45,92,.10);
}

.nav-username{
  font-size: 0.9rem;
  color: var(--ppk-text);
  font-weight: 600;
}

/* dropdown */
.profile-dropdown-menu{
  min-width: 260px;
  border-radius: 12px;
  border: 1px solid rgba(15,45,92,.10);
}

/* ให้ dropdown item มี hover เข้าธีม */
.profile-dropdown-menu .dropdown-item{
  border-radius: 10px;
  margin-inline: 8px;
  width: calc(100% - 16px);
}

.profile-dropdown-menu .dropdown-item:hover{
  background-color: var(--ppk-soft);
  color: var(--ppk-blue);
}

/* Mobile menu */
.mobile-profile-card{
  border-radius: 12px;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  background-color: #ffffff;
}

.custom-toggler{
  border-color: rgba(15,45,92,.25);
}

/* ===== Pinwheel layout: navbar เริ่มหลัง sidebar ===== */
@media (min-width: 1024px){
  .navbar-pinwheel{
    left: 260px;               /* sidebar กว้าง 260px */
    width: calc(100% - 260px);
  }
}
</style>
