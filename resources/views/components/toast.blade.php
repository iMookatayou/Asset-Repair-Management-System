@php
  $toast = session('toast');
  if ($toast) {
      session()->forget('toast');
  }

  // Base fields
  $type     = $toast['type']     ?? null;      // success|info|warning|error
  $message  = $toast['message']  ?? null;
  $position = $toast['position'] ?? 'tc';      // tr|tl|br|bl|center|tc|bc|uc
  $timeout  = (int)($toast['timeout'] ?? 3200);
  $size     = $toast['size']     ?? 'lg';      // sm|md|lg

  // fallback จาก errors / session อื่น ๆ
  $firstError = (isset($errors) && method_exists($errors,'first') && $errors->any()) ? $errors->first() : null;
  if (!$message && $firstError) {
    $message = $firstError;
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

  // helper asset รองรับ https
  $isHttps = request()->isSecure();
  $link = function(string $path) use ($isHttps) {
      return $isHttps ? secure_asset($path) : asset($path);
  };

  // ใช้ lottieMap จาก Controller ถ้ามี ถ้าไม่มีก็ default
  if (!isset($lottieMap) || !is_array($lottieMap)) {
      $lottieMap = [
        'success' => $link('lottie/lock_with_green_tick.json'),
        'info'    => $link('lottie/lock_with_blue_info.json'),
        'warning' => $link('lottie/lock_with_yellow_alert.json'),
        'error'   => $link('lottie/lock_with_red_tick.json'),
      ];
  }
@endphp

<style>
  .toast-overlay{position:fixed;inset:0;z-index:100001;pointer-events:none}
  .toast-pos{display:flex;width:100%;height:100%;padding:1rem}
  .toast-pos.tr{align-items:flex-start;justify-content:flex-end}
  .toast-pos.tl{align-items:flex-start;justify-content:flex-start}
  .toast-pos.br{align-items:flex-end;justify-content:flex-end}
  .toast-pos.bl{align-items:flex-end;justify-content:flex-start}
  .toast-pos.center{align-items:center;justify-content:center}
  .toast-pos.tc{align-items:flex-start;justify-content:center;padding-top:calc(var(--topbar-h,0px) + .75rem)}
  .toast-pos.uc{align-items:flex-start;justify-content:center;padding-top:15vh}
  .toast-pos.bc{align-items:flex-end;justify-content:center;padding-bottom:.75rem}

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

  @media (max-width:480px){ .toast-card{ min-width: calc(100vw - 2rem); } }
</style>

<div class="toast-overlay" aria-live="polite" aria-atomic="true"></div>

<script id="lottie-player-loader" src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" async></script>

<script>
(function(){
  const LOTTIE = {
    success: @json($lottieMap['success'] ?? null),
    info:    @json($lottieMap['info'] ?? null),
    warning: @json($lottieMap['warning'] ?? null),
    error:   @json($lottieMap['error'] ?? null),
  };

  const FORCE_POSITION = 'tc';

  function ensurePos(position){
    const overlay = document.querySelector('.toast-overlay');
    overlay.innerHTML = '';
    const posEl = document.createElement('div');
    posEl.className = 'toast-pos ' + position;
    overlay.appendChild(posEl);
    return { overlay, posEl };
  }

  function showToast({type='info', message='', position='tc', timeout=3200, size='lg'} = {}){
    position = FORCE_POSITION || position || 'tc';
    const allowed = ['tr','tl','br','bl','center','tc','bc','uc'];
    if (!allowed.includes(position)) position = 'tc';
    timeout = Number(timeout) || 3200;

    const { posEl } = ensurePos(position);

    const card = document.createElement('section');
    const sizeClass = (['sm','md','lg'].includes(size) ? `toast--${size}` : 'toast--lg');

    // ❗ เปลี่ยนจาก toast-${type} → toast--${type} เพื่อไม่ให้ไปชน CSS เดิม (.toast-warning)
    card.className = `toast-card ${sizeClass} toast--${type}`;
    card.setAttribute('role','status');

    const icon = document.createElement('div');
    icon.className = 'toast-icon';
    const src = LOTTIE[type] || LOTTIE.success;

    function renderPlaceholder(){
      icon.innerHTML = `<span style="display:inline-block;width:var(--toast-icon);height:var(--toast-icon);border-radius:50%;background:#e2e8f0"></span>`;
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
    } catch (e){
      renderPlaceholder();
    }

    const msg = document.createElement('div');
    msg.className = 'toast-msg';
    msg.textContent = message ?? '';

    const btn = document.createElement('button');
    btn.className = 'toast-close';
    btn.setAttribute('aria-label','Close');
    btn.innerHTML = '&times;';

    const bar = document.createElement('div');
    bar.className = 'toast-bar';
    const fill = document.createElement('div');
    fill.className = `toast-fill fill-${type}`;
    bar.appendChild(fill);

    card.append(icon, msg, btn, bar);
    posEl.appendChild(card);

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

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    }, { once:true });
  }

  // expose
  window.showToast = showToast;
  window.addEventListener('app:toast', e => showToast(e.detail || {}));

  @if ($type && $message)
  (function fireToastNowOrReady(){
    const payload = {
      type: @json($type),
      message: @json($message),
      position: @json($position),
      timeout: @json($timeout),
      size: @json($size ?? 'lg')
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => window.showToast(payload), { once: true });
    } else {
      window.showToast(payload);
    }
  })();
  @endif
})();
</script>
