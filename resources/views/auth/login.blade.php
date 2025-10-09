<x-guest-layout>
    {{-- ====== THEME: Bangkok Hospital Chanthaburi ====== --}}
    <style>
        :root{
            --bh-blue: #0b4aa2;   /* น้ำเงินหลัก */
            --bh-blue-600:#1056b6;
            --bh-blue-100:#eaf2ff;
            --bh-slate:#334155;
            --bh-border:#e5e7eb;
            --bh-danger:#d32f2f;
            --bh-bg:#f8fafc;     /* พื้นหลังโทนอ่อน */
        }
        html,body{background:var(--bh-bg);}
        .login-wrap{
            min-height:100dvh; display:grid; place-items:center; padding:24px;
            background:
              radial-gradient(1200px 600px at 120% -10%, #e6eefc 0%, rgba(255,255,255,0) 60%),
              radial-gradient(800px 500px at -10% 110%, #e9f3ff 0%, rgba(255,255,255,0) 60%);
        }
        .login-card{
            width:min(920px,95vw);
            display:grid; grid-template-columns: 1.1fr 0.9fr; gap:0;
            background:#fff; border:1px solid var(--bh-border); border-radius:20px; overflow:hidden;
            box-shadow:0 10px 30px rgba(16,86,182,.08);
        }
        .login-side{
            padding:32px 32px 28px 32px; position:relative; isolation:isolate;
        }
        .brand{
            display:flex; align-items:center; gap:12px; margin-bottom:18px;
        }
        .brand img{
            width:42px; height:42px; object-fit:contain;
            filter: drop-shadow(0 2px 6px rgba(16,86,182,.2));
        }
        .brand h1{
            margin:0; font-size:18px; line-height:1.2; color:var(--bh-blue);
            font-weight:700; letter-spacing:.2px;
        }
        .subtle{
            color:#64748b; font-size:13px; margin-bottom:18px;
        }
        .pill{
            display:inline-flex; align-items:center; gap:8px;
            background:var(--bh-blue-100); color:var(--bh-blue);
            border-radius:999px; padding:6px 10px; font-size:12px; font-weight:600;
        }
        .divider{ height:1px; background:var(--bh-border); margin:16px 0; }
        .alert{ background:#fff5f5; color:#b42318; border:1px solid #fecaca; padding:8px 10px; font-size:12px; border-radius:10px; }

        /* Form */
        .field{ margin-top:14px; }
        .label{ display:block; font-size:13px; color:#334155; margin-bottom:6px; font-weight:600;}
        .input, .checkbox{
            width:100%; border:1px solid var(--bh-border); border-radius:12px;
            padding:10px 12px; font-size:14px; background:#fff; color:#0f172a;
            outline:none; transition:.15s border, .15s box-shadow;
        }
        .input:focus{
            border-color:var(--bh-blue); box-shadow:0 0 0 3px rgba(16,86,182,.15);
        }
        .row{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:12px;}
        .link{
            color:var(--bh-blue); text-decoration:none; font-size:13px; font-weight:600;
        }
        .link:hover{ text-decoration:underline;}
        .btn{
            appearance:none; border:none; cursor:pointer;
            background:var(--bh-blue); color:#fff; padding:10px 14px; border-radius:12px;
            font-weight:700; font-size:14px; letter-spacing:.2px; transition:.2s transform, .2s box-shadow, .2s background;
        }
        .btn:hover{ background:var(--bh-blue-600); box-shadow:0 6px 16px rgba(16,86,182,.22); transform:translateY(-1px);}
        .remember{ display:inline-flex; align-items:center; gap:8px; font-size:13px; color:#475569; }
        .checkbox{ width:auto; border-radius:8px;}

        /* Hero side */
        .hero{
            background:linear-gradient(135deg, #0c3f8f 0%, #0b4aa2 40%, #0e66d6 100%);
            color:#eaf2ff; padding:28px 28px 24px 28px; position:relative;
            display:flex; flex-direction:column; justify-content:space-between;
        }
        .hero h2{ margin:8px 0 8px 0; font-size:22px; line-height:1.25; font-weight:800;}
        .hero p{ margin:0; color:#dbeafe; font-size:14px; }
        .hero-card{
            margin-top:16px; background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.25); border-radius:16px; padding:14px;
            backdrop-filter: blur(4px);
        }
        .badge{ display:inline-block; font-size:11px; padding:4px 8px; border-radius:999px; background:#fff; color:#0b4aa2; font-weight:800; }
        .hero small{ color:#cfe4ff; }

        /* Responsive */
        @media (max-width: 860px){
            .login-card{ grid-template-columns: 1fr; }
            .hero{ order:-1; border-bottom-left-radius:0; border-bottom-right-radius:0; }
        }
    </style>

    <div class="login-wrap">
        <div class="login-card">

            {{-- HERO RIGHT (หรือจะสลับซ้าย/ขวาได้) --}}
            <aside class="hero">
                <div>
                    <span class="badge">BCH Chanthaburi</span>
                    <h2>ระบบแจ้งซ่อมบำรุง<br>Bangkok Hospital จันทบุรี</h2>
                    <p>ปลอดภัย รวดเร็ว ตรวจสอบสถานะได้แบบเรียลไทม์</p>

                    <div class="hero-card" style="margin-top:16px">
                        <small>ติดต่อฝ่ายซ่อมบำรุง: 039-xxx-xxx • เวลาให้บริการ 08:00–17:00 น.</small>
                    </div>
                </div>
                <div style="opacity:.9">
                    <small>© {{ date('Y') }} Bangkok Hospital Chanthaburi</small>
                </div>
            </aside>

            {{-- FORM LEFT --}}
            <section class="login-side">
                <div class="brand">
                    {{-- เปลี่ยนพาธโลโก้ตามไฟล์จริงของคุณ: public/images/hospital-logo.svg --}}
                    <img src="{{ asset('images/hospital-logo.svg') }}" alt="Bangkok Hospital Chanthaburi logo" onerror="this.style.display='none'">
                    <div>
                        <h1>Bangkok Hospital จันทบุรี</h1>
                        <div class="subtle">Asset Repair Management • เข้าสู่ระบบ</div>
                    </div>
                </div>

                {{-- Session Status --}}
                @if (session('status'))
                    <div class="alert" role="alert">{{ session('status') }}</div>
                @endif

                {{-- Validation Errors (สรุป) --}}
                @if ($errors->any())
                    <div class="alert" role="alert">
                        {{ __('กรุณาตรวจสอบข้อมูลให้ถูกต้อง') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="field">
                        <label for="email" class="label">{{ __('อีเมล') }}</label>
                        <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus autocomplete="username">
                        @error('email') <div class="subtle" style="color:var(--bh-danger)">{{ $message }}</div> @enderror
                    </div>

                    {{-- Password --}}
                    <div class="field">
                        <label for="password" class="label">{{ __('รหัสผ่าน') }}</label>
                        <input id="password" name="password" type="password" class="input" required autocomplete="current-password">
                        @error('password') <div class="subtle" style="color:var(--bh-danger)">{{ $message }}</div> @enderror
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="row">
                        <label for="remember_me" class="remember">
                            <input id="remember_me" type="checkbox" class="checkbox" name="remember">
                            <span>{{ __('จดจำฉันไว้ในระบบ') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="link" href="{{ route('password.request') }}">
                                {{ __('ลืมรหัสผ่าน?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <div class="row" style="margin-top:16px">
                        <button class="btn" type="submit">{{ __('เข้าสู่ระบบ') }}</button>
                    </div>
                </form>

                {{-- หมายเหตุเล็กน้อย --}}
                <div class="subtle" style="margin-top:14px">
                    * ระบบนี้เชื่อมต่อกับ <strong>Sanctum</strong> เพื่อความปลอดภัยในการเข้าถึง API
                </div>

                {{-- โลโก้รอง/ตรากาชาด (ถ้ามี) --}}
                <div style="margin-top:18px">
                    <span class="pill">Hospital • Safety First</span>
                </div>
            </section>

        </div>
    </div>
</x-guest-layout>
