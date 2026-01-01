<footer class="footer-hero mt-auto text-light py-3">
  <div class="footer-inner">
    <div class="container-fluid px-3 px-lg-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="fw-semibold small">
          © {{ date('Y') }} Phrapokklao Hospital — Information Technology Group
        </div>
        <div class="small opacity-75">
          Asset Repair Management System • Build {{ app()->version() }}
        </div>
      </div>
    </div>
  </div>
</footer>

<style>
  /* ====== Footer (ไม่ลอย ไม่ fixed) ====== */
  .footer-hero{
    position: static !important;         /* กันเคสที่เคย fixed มาก่อน */
    width: 100%;
    margin-top: auto;                    /* ทำงานเมื่อ parent เป็น flex-column */

    background-color: #0F2D5C;
    color: #EAF2FF;
    font-family: 'Sarabun', system-ui, sans-serif;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,.12);
  }

  .footer-hero a{
    color: #EAF2FF;
    text-decoration: none;
  }
  .footer-hero a:hover{
    color: #ffffff;
    text-decoration: underline;
  }

  /* ====== ทำให้หน้าสั้นก็ชิดล่าง (ต้องให้ #main เป็น flex column) ======
     ถ้าคุณมี #main อยู่แล้ว ให้เพิ่มเฉพาะบล็อกนี้ (ไม่กระทบหน้าอื่น) */
  #main{
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - var(--nav-h, 0px));  /* ถ้ามี navbar fixed ด้านบน */
  }

  /* ====== หลบ sidebar เฉพาะส่วนเนื้อใน footer (พื้นหลังยังเต็มจอ) ====== */
  @media (min-width: 1024px){
    .layout.with-expanded  .footer-inner{ padding-left: var(--side-w); }
    .layout.with-compact   .footer-inner{ padding-left: var(--side-w-compact); }
    .layout.with-collapsed .footer-inner{ padding-left: var(--side-w-collapsed); }
  }
  @media (max-width: 1023px){
    .footer-inner{ padding-left: 0; }
  }
</style>
