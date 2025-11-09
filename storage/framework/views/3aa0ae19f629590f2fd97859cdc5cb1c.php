<?php
  // ===== อ่าน toast จาก session =====
  $toast = session('toast');
  if ($toast) {
      // ใช้ครั้งเดียวจบ
      session()->forget('toast');
  }

  // Base fields
  $type     = $toast['type']     ?? null;      // success|info|warning|error
  $message  = $toast['message']  ?? null;
  $position = $toast['position'] ?? 'tc';      // tr|tl|br|bl|center|tc|bc|uc (บังคับใช้ tc เป็นค่าเริ่มต้น)
  $timeout  = (int)($toast['timeout'] ?? 3200);
  $size     = $toast['size']     ?? 'lg';      // sm|md|lg

  // ===== map error/status → toast อัตโนมัติ =====
  $firstError = (isset($errors) && method_exists($errors,'first') && $errors->any()) ? $errors->first() : null;
  if (!$message && $firstError) {
    $message = $firstError;
    // ใช้ warning สำหรับฟอร์มไม่ผ่าน เพื่อสอดคล้องกับ UX ของระบบ (Alert)
    $type    = $type ?: 'warning';
  }
  if (!$message && session('error')) {
      $message = session('error');
      $type    = $type ?: 'error';
  }
  if (!$message && session('status')) {
      $message = session('status');
      $type    = $type ?: 'success';
  }

  // ===== Lottie map (บังคับ scheme ให้ตรงกับหน้า ป้องกัน Mixed Content) =====
  $isHttps = request()->isSecure();
  $link = function(string $path) use ($isHttps) {
      return $isHttps ? secure_asset($path) : asset($path);
  };
  // เส้นทาง asset() ต้องไม่ใส่ /public นำหน้า (Laravel จะชี้ไป public/ ให้อัตโนมัติ)
  $lottieMap = [
    'success' => $link('lottie/lock_with_green_tick.json'),
    'info'    => $link('lottie/lock_with_blue_info.json'),
    'warning' => $link('lottie/lock_with_yello_alert.json'), // yello ตามชื่อไฟล์จริง
    'error'   => $link('lottie/lock_with_red_tick.json'),
  ];
?>

<style>
  .toast-overlay{position:fixed;inset:0;z-index:100001;pointer-events:none}
  .toast-pos{display:flex;width:100%;height:100%;padding:1rem}
  .toast-pos.tr{align-items:flex-start;justify-content:flex-end}
  .toast-pos.tl{align-items:flex-start;justify-content:flex-start}
  .toast-pos.br{align-items:flex-end;justify-content:flex-end}
  .toast-pos.bl{align-items:flex-end;justify-content:flex-start}
  .toast-pos.center{align-items:center;justify-content:center}
  .toast-pos.tc{align-items:flex-start;justify-content:center;padding-top:calc(var(--topbar-h,0px) + .75rem)}
  /* upper-center (ระหว่าง top กับ center เลื่อนลงมาเล็กน้อย) */
  .toast-pos.uc{align-items:flex-start;justify-content:center;padding-top:15vh}
  .toast-pos.bc{align-items:flex-end;justify-content:center;padding-bottom:.75rem}

  /* ============ Design tokens ============ */
  .toast-card{
    --toast-max-w: min(92vw, 640px);
    --toast-min-w: 420px;
    --toast-pad-x: 22px;
    --toast-pad-y: 16px;
    --toast-fs: 16px;
    --toast-icon: 36px;
    --toast-radius: 14px;
    --toast-bar-h: 4px;

    pointer-events:auto;
    width: max-content;
    max-width: var(--toast-max-w);
    min-width: var(--toast-min-w);

    background:#fff;
    border-radius: var(--toast-radius);
    border:1px solid #e5eef7;
    box-shadow:0 14px 48px rgba(15,23,42,.14);

    opacity:0;
    transform:translateY(-6px);
    transition:opacity .22s ease, transform .22s ease;

    display:flex;
    align-items:center;
    gap:.9rem;
    padding: var(--toast-pad-y) var(--toast-pad-x);
    position:relative;
    overflow:hidden;
  }
  .toast-card.show{ opacity:1; transform:translateY(0); }

  /* ขนาด */
  .toast--sm{
    --toast-max-w: min(92vw, 420px);
    --toast-min-w: 320px;
    --toast-pad-x: 16px;
    --toast-pad-y: 10px;
    --toast-fs: 14px;
    --toast-icon: 28px;
    --toast-radius: 12px;
    --toast-bar-h: 3px;
  }
  .toast--md{
    --toast-max-w: min(92vw, 520px);
    --toast-min-w: 380px;
    --toast-pad-x: 18px;
    --toast-pad-y: 14px;
    --toast-fs: 15px;
    --toast-icon: 32px;
    --toast-radius: 12px;
    --toast-bar-h: 4px;
  }
  .toast--lg{
    --toast-max-w: min(92vw, 680px);
    --toast-min-w: 440px;
    --toast-pad-x: 24px;
    --toast-pad-y: 18px;
    --toast-fs: 16px;
    --toast-icon: 36px;
    --toast-radius: 16px;
    --toast-bar-h: 4px;
  }

  .toast-icon{flex:0 0 var(--toast-icon);display:flex;align-items:center;justify-content:center}
  .toast-msg{font-size:var(--toast-fs);color:#0f172a;line-height:1.5;white-space:normal;word-break:break-word;flex:1}
  .toast-close{border:0;background:transparent;font-size:calc(var(--toast-fs) + 1px);color:#64748b;cursor:pointer;line-height:1}
  .toast-close:hover{ color:#0f172a; }

  /* Progress bar */
  .toast-bar{position:absolute;bottom:0;left:0;height:var(--toast-bar-h);width:100%;background:#f1f5f9}
  .toast-fill{height:var(--toast-bar-h);width:0;transition:width linear}
  .fill-success{background:#10b981}
  .fill-info{background:#3b82f6}
  .fill-warning{background:#f59e0b}
  .fill-error{background:#ef4444}

  /* Mobile safety */
  @media (max-width:480px){ .toast-card{ min-width: calc(100vw - 2rem); } }
</style>

<div class="toast-overlay" aria-live="polite" aria-atomic="true"></div>


<script id="lottie-player-loader" src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" async></script>

<script>
(function(){
  // ===== แผนที่ไฟล์ Lottie จาก PHP =====
  const LOTTIE = {
    success: <?php echo json_encode($lottieMap['success'] ?? null, 15, 512) ?>,
    info:    <?php echo json_encode($lottieMap['info'] ?? null, 15, 512) ?>,
    warning: <?php echo json_encode($lottieMap['warning'] ?? null, 15, 512) ?>,
    error:   <?php echo json_encode($lottieMap['error'] ?? null, 15, 512) ?>,
  };

  // ตั้งค่าเป็นค่า truthy ('tc' ฯลฯ) หากอยาก "บังคับ" ตำแหน่งทั้งหมดให้ตรงกัน
  const FORCE_POSITION = 'tc'; // บังคับ Toast ทุกตัวให้อยู่ top-center

  function ensurePos(position){
    const overlay = document.querySelector('.toast-overlay');
    overlay.innerHTML = '';
    const posEl = document.createElement('div');
    posEl.className = 'toast-pos ' + position;
    overlay.appendChild(posEl);
    return { overlay, posEl };
  }

  /*
    showToast options:
    - type: 'success'|'info'|'warning'|'error'
    - message: string
    - position: 'tr'|'tl'|'br'|'bl'|'center'|'tc'|'bc'
    - timeout: milliseconds
    - size: 'sm'|'md'|'lg'
  */
  function showToast({type='info', message='', position='tc', timeout=3200, size='lg'} = {}){
    position = FORCE_POSITION || position || 'tc';
  const allowed = ['tr','tl','br','bl','center','tc','bc','uc'];
    if (!allowed.includes(position)) position = 'tc';
    timeout = Number(timeout) || 3200;

    const { posEl } = ensurePos(position);

    const card = document.createElement('section');
    const sizeClass = (['sm','md','lg'].includes(size) ? `toast--${size}` : 'toast--lg');
    card.className = `toast-card ${sizeClass} toast-${type}`;
    card.setAttribute('role','status');

    // icon
    const icon = document.createElement('div');
    icon.className = 'toast-icon';
    const src = LOTTIE[type] || LOTTIE.success;
    function renderPlaceholder(){
      icon.innerHTML = `<span style=\"display:inline-block;width:var(--toast-icon);height:var(--toast-icon);border-radius:50%;background:#e2e8f0\"></span>`;
    }
    function renderLottie(){
      if (!src) return renderPlaceholder();
      icon.innerHTML = `<lottie-player src="${src}" renderer="svg" style="width:var(--toast-icon);height:var(--toast-icon)" background="transparent" speed="1" autoplay></lottie-player>`;
    }
    try{
      if (window.customElements && (customElements.get('lottie-player') || customElements.whenDefined)){
        if (customElements.get('lottie-player')) {
          renderLottie();
        } else {
          renderPlaceholder();
          customElements.whenDefined('lottie-player').then(renderLottie).catch(()=>{});
        }
      } else {
        renderLottie();
      }
    } catch (e){ renderPlaceholder(); }

    // message (ใช้ textContent เพื่อความปลอดภัย)
    const msg = document.createElement('div');
    msg.className = 'toast-msg';
    msg.textContent = message ?? '';

    // close button
    const btn = document.createElement('button');
    btn.className = 'toast-close';
    btn.setAttribute('aria-label','Close');
    btn.innerHTML = '&times;';

    // progress
    const bar = document.createElement('div');
    bar.className = 'toast-bar';
    const fill = document.createElement('div');
    fill.className = `toast-fill fill-${type}`;
    bar.appendChild(fill);

    card.append(icon, msg, btn, bar);
    posEl.appendChild(card);

    // Animate in + progress
    requestAnimationFrame(() => {
      card.classList.add('show');
      requestAnimationFrame(() => {
        fill.style.transition = `width ${timeout}ms linear`;
        fill.style.width = '100%';
      });
    });

    let timer = setTimeout(close, timeout + 60);

    function close(){
      card.classList.remove('show');
      setTimeout(()=> card.remove(), 200);
    }

    btn.addEventListener('click', close);

    // Pause on hover
    card.addEventListener('mouseenter', () => {
      fill.style.transition = 'none';
      const w = getComputedStyle(fill).width;
      fill.style.width = w;
      clearTimeout(timer);
    });
    card.addEventListener('mouseleave', () => {
      const done = parseFloat(getComputedStyle(fill).width) / card.clientWidth;
      const remainMs = Math.max(0, 1 - done) * timeout;
      requestAnimationFrame(() => {
        fill.style.transition = `width ${remainMs}ms linear`;
        fill.style.width = '100%';
      });
      timer = setTimeout(close, remainMs + 50);
    });

    // ESC to close
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); }, { once:true });
  }

  // Expose
  window.showToast = showToast;
  window.addEventListener('app:toast', e => showToast(e.detail || {}));

  // ===== ยิงอัตโนมัติถ้ามีค่าใน session =====
  <?php if($type && $message): ?>
  (function fireToastNowOrReady(){
    const payload = {
      type: <?php echo json_encode($type, 15, 512) ?>,
      message: <?php echo json_encode($message, 15, 512) ?>,
      position: <?php echo json_encode($position, 15, 512) ?>,
      timeout: <?php echo json_encode($timeout, 15, 512) ?>,
      size: <?php echo json_encode($size ?? 'lg', 15, 512) ?>
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => window.showToast(payload), { once: true });
    } else {
      window.showToast(payload);
    }
  })();
  <?php endif; ?>
})();
</script>
<?php /**PATH C:\Users\Developer\development\Asset-Repair-Management-System\resources\views/components/toast.blade.php ENDPATH**/ ?>