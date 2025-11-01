<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Asset Repair Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    :root{
      /* ชุดสีน้ำเงินเดียว (Monochrome) แนวรพ.กรุงเทพ */
      --blue-950:#0B1E34; /* midnight navy */
      --blue-900:#0F2D5C; /* deep navy */
      --blue-800:#123A62; /* dark steel blue */
      --blue-700:#174A97; /* royal-ish */
      --blue-600:#1E63D6; /* bright primary */
      --blue-100:#E9F2FF;
      --text-on-dark:#EAF2FF;
    }

    /* ===== NAVBAR HERO (โทนเดียว น้ำเงินไล่ระดับ) ===== */
    .navbar-hero{
      background: linear-gradient(90deg, var(--blue-950) 0%, var(--blue-900) 35%, var(--blue-800) 65%, var(--blue-700) 100%);
      color: var(--text-on-dark);
      padding-top: 1rem !important;
      padding-bottom: 1rem !important;
      min-height: 86px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
      border-bottom: 1px solid rgba(255,255,255,.12);
      backdrop-filter: blur(6px);
    }
    .navbar-hero .navbar-brand{
      font-weight: 800;
      letter-spacing: .2px;
      font-size: 1.45rem;
      line-height: 1;
      color: #fff;
    }
    .brand-kicker{ font-size:.82rem; opacity:.9; margin-top:.15rem; }

    .logo-wrap{
      width:40px; height:40px; border-radius:12px;
      background: rgba(255,255,255,.12);
      display:inline-flex; align-items:center; justify-content:center;
      overflow:hidden;
      border:1px solid rgba(255,255,255,.18);
    }
    .logo-wrap img{ width:28px; height:28px; object-fit:contain; }

    .avatar-ring{
      width:40px; height:40px; border-radius:50%;
      border:2px solid rgba(255,255,255,.55);
      background:#fff;
    }

    /* ===== SUBBAR (เป็นน้ำเงินทึบ) ===== */
    .subbar{
      background: linear-gradient(90deg, var(--blue-950) 0%, var(--blue-900) 100%);
      color: var(--text-on-dark);
      border-bottom: 1px solid rgba(255,255,255,.08);
      height:56px;
    }
    .subbar a{ color:#CFE1FF; }
    .subbar .btn-outline-light{
      --bs-btn-color:#EAF2FF;
      --bs-btn-border-color:rgba(255,255,255,.35);
      --bs-btn-hover-bg:rgba(255,255,255,.1);
      --bs-btn-hover-border-color:rgba(255,255,255,.55);
    }
    .subbar .btn-primary{
      --bs-btn-bg:var(--blue-600); --bs-btn-border-color:transparent;
      --bs-btn-hover-bg:#1b58c2;
      --bs-btn-color:#fff;
    }

    /* ===== BADGES (น้ำเงินโทนเดียว) ===== */
    .status-badge{
      font-size:.75rem; border-radius:999px; padding:.18rem .6rem;
      border:1px solid rgba(0,0,0,.06); background:#fff;
    }
    .status-total{ background:var(--blue-100); color:var(--blue-900); border-color:#cfe1ff; }
    .status-pending{ background:#DDE9FF; color:#184a96; border-color:#cfe1ff; }
    .status-progress{ background:#CFE1FF; color:#0F2D5C; border-color:#bcd6ff; }
    .status-done{ background:#BFD6FF; color:#0B1E34; border-color:#abc9ff; }

    /* เนื้อหา */
    body{ background: #f5f7fb; padding-top: 86px; }
    .card-dark{
      background: linear-gradient(180deg, var(--blue-950) 0%, var(--blue-900) 100%);
      color:#dfe9ff; border:1px solid rgba(255,255,255,.08);
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-hero">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <span class="logo-wrap">
          <!-- เปลี่ยนเป็นโลโก้ของคุณ -->
          <img src="{{ asset('images/logoppk.png') }}" alt="PPK Logo" class="brand-logo">
        </span>
        <span>
          Asset Repair
          <div class="brand-kicker fw-normal">Management Dashboard</div>
        </span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topNav">
        <!-- ช่องค้นหา (กลาง) -->
        <form class="ms-lg-5 me-lg-auto d-none d-lg-block" role="search" style="min-width:360px;">
          <div class="input-group">
            <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
            <input class="form-control border-0" type="search" placeholder="ค้นหาเลขใบซ่อม / ผู้แจ้ง / รายการ..." aria-label="Search">
          </div>
        </form>

        <ul class="navbar-nav ms-auto align-items-center gap-2">
          <li class="nav-item d-none d-sm-block">
            <a class="btn btn-sm btn-light text-primary fw-semibold rounded-pill" href="#">
              <i class="bi bi-plus-lg me-1"></i> แจ้งซ่อมใหม่
            </a>
          </li>
          <li class="nav-item">
            <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-2 py-1 border border-white border-opacity-25">
              <img src="https://ui-avatars.com/api/?name=Admin&background=ffffff&color=1E63D6" class="avatar-ring" alt="user">
              <div class="d-none d-md-block">
                <div class="fw-semibold">Admin</div>
                <div class="small opacity-75">administrator</div>
              </div>
            </div>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-light rounded-3" href="#">
              <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- SUBBAR -->
  <div class="subbar d-flex align-items-center">
    <div class="container-fluid d-flex align-items-center justify-content-between">
      <nav class="small">
        <a href="#" class="text-decoration-none me-1">หน้าหลัก</a> /
        <a href="#" class="text-decoration-none mx-1">งานซ่อมบำรุง</a> /
        <span class="fw-semibold text-white ms-1">Dashboard</span>
      </nav>
      <div class="d-none d-md-flex gap-2">
        <a class="btn btn-outline-light rounded-3" href="#"><i class="bi bi-gear me-1"></i>จัดการ</a>
        <a class="btn btn-primary rounded-3" href="#"><i class="bi bi-box-arrow-in-down me-1"></i>นำเข้า</a>
      </div>
    </div>
  </div>

  <!-- CONTENT -->
  <main class="container-fluid py-4">
    <!-- หัวข้อ + badges (แถบแบ่งสีถูกลบออกแล้ว) -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
      <div class="fs-5 fw-bold text-dark">Asset Repair Dashboard</div>
      <div class="d-flex align-items-center gap-2 ms-auto">
        <span class="status-badge status-total">Total: <strong>300</strong></span>
        <span class="status-badge status-pending">Pending: <strong>103</strong></span>
        <span class="status-badge status-progress">In progress: <strong>107</strong></span>
        <span class="status-badge status-done">Completed: <strong>90</strong></span>
      </div>
    </div>

    <!-- demo cards -->
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card card-dark border-0">
          <div class="card-body">
            <h6 class="card-title mb-3">Monthly trend 6 months</h6>
            <div class="ratio ratio-16x9 bg-black bg-opacity-25 rounded-3"></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card card-dark border-0">
          <div class="card-body">
            <h6 class="card-title mb-3">Asset types Top 8 plus others</h6>
            <div class="ratio ratio-16x9 bg-black bg-opacity-25 rounded-3"></div>
          </div>
        </div>
      </div>
      <!-- เพิ่มการ์ดอื่นได้ตามต้องการ -->
    </div>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
