@php
  $toast = session('toast');
  if ($toast) { session()->forget('toast'); }

  $type     = $toast['type']     ?? null;      // success|info|warning|error
  $message  = $toast['message']  ?? null;
  $position = $toast['position'] ?? 'tr';      // tr|tl|br|bl
  $timeout  = (int)($toast['timeout'] ?? 3800);
  $size     = $toast['size']     ?? 'lg';      // sm|md|lg|xl

  $firstError = (isset($errors) && method_exists($errors,'first') && $errors->any()) ? $errors->first() : null;
  if (!$message && $firstError) { $message = $firstError; $type = $type ?: 'warning'; }
  if (!$message && session('error'))  { $message = session('error');  $type = $type ?: 'error'; }
  if (!$message && session('status')) { $message = session('status'); $type = $type ?: 'success'; }
@endphp

<style>
  :root{
    --toast-z: 100001;
    --toast-gap: 12px;

    --toast-max-w: min(92vw, 460px);
    --toast-min-w: 340px;

    --toast-radius: 8px;
    --toast-shadow: 0 12px 30px rgba(15,23,42,.24);
    --toast-border: rgba(255,255,255,.20);

    --toast-pad-x: 16px;
    --toast-pad-y: 14px;

    --toast-title-fs: 15px;
    --toast-msg-fs: 15px;

    --toast-icon: 34px;
    --toast-icon-box: 42px;

    --toast-bar-h: 4px;
  }

  .toast-overlay{
    position:fixed; inset:0;
    z-index:var(--toast-z);
    pointer-events:none;
  }

  .toast-pos{
    width:100%; height:100%;
    display:flex;
    flex-direction:column;
    gap: var(--toast-gap);
    padding: 14px;
  }
  .toast-pos.tr{
    align-items:flex-end;
    justify-content:flex-start;
    padding-top: calc(var(--topbar-h, 0px) + 14px);
  }
  .toast-pos.tl{
    align-items:flex-start;
    justify-content:flex-start;
    padding-top: calc(var(--topbar-h, 0px) + 14px);
  }
  .toast-pos.br{ align-items:flex-end; justify-content:flex-end; }
  .toast-pos.bl{ align-items:flex-start; justify-content:flex-end; }

  .toast-card{
    pointer-events:auto;
    width: min(100%, var(--toast-max-w));
    min-width: var(--toast-min-w);

    border-radius: var(--toast-radius);
    box-shadow: var(--toast-shadow);
    border: 1px solid var(--toast-border);

    position:relative;
    overflow:hidden;

    opacity:0;
    transform: translateY(-10px);
    transition: opacity .18s ease, transform .18s ease;

    outline:none !important;
  }
  .toast-card.show{ opacity:1; transform: translateY(0); }

  .toast-inner{
    display:flex;
    align-items:center;
    gap: 12px;
    padding: var(--toast-pad-y) var(--toast-pad-x);
    color:#fff;
  }
  .toast-ico{
    flex:0 0 var(--toast-icon-box);
    width:var(--toast-icon-box);
    height:var(--toast-icon-box);
    display:grid;
    place-items:center;
    border-radius: 6px;
    background: rgba(255,255,255,.18);
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.14);
  }
  .toast-ico svg{
    width: var(--toast-icon);
    height: var(--toast-icon);
    display:block;
    fill: currentColor;
    color:#fff;
    filter: drop-shadow(0 1px 0 rgba(0,0,0,.18));
  }

  .toast-text{ flex:1; min-width:0; }

  .toast-title{
    font-size: var(--toast-title-fs);
    font-weight: 900;
    letter-spacing:.01em;
    margin: 0 0 4px 0;
    text-shadow: 0 1px 0 rgba(0,0,0,.18);
  }

  .toast-msg{
    font-size: var(--toast-msg-fs);
    line-height: 1.45;
    margin:0;
    opacity:.96;

    display:-webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow:hidden;
    text-overflow: ellipsis;
    word-break: break-word;
  }

  .toast-close{
    border:0;
    background: rgba(255,255,255,.18);
    color:#fff;
    width: 34px; height: 34px;
    border-radius: 6px;
    cursor:pointer;
    display:grid;
    place-items:center;
    font-size: 20px;
    line-height:0;
    transition: background .12s ease;
    outline:none !important;
  }
  .toast-close:hover{ background: rgba(255,255,255,.30); }
  .toast-close:focus,
  .toast-close:focus-visible{ outline:none !important; box-shadow:none; }

  .toast-bar{
    height: var(--toast-bar-h);
    background: rgba(0,0,0,.18);
  }
  .toast-fill{
    height: var(--toast-bar-h);
    width:0;
    transition: width linear;
    background: rgba(255,255,255,.58);
  }

  .toast--success{ background:#6ea35e; }
  .toast--error  { background:#b5564c; }
  .toast--warning{ background:#e3a23a; }
  .toast--info   { background:#5a9db5; }

  .toast--sm{
    --toast-max-w: min(92vw, 420px);
    --toast-min-w: 320px;
    --toast-pad-x: 14px;
    --toast-pad-y: 12px;
    --toast-title-fs: 14px;
    --toast-msg-fs: 14px;
    --toast-icon: 30px;
    --toast-icon-box: 38px;
    --toast-bar-h: 3px;
  }
  .toast--md{
    --toast-max-w: min(92vw, 440px);
    --toast-min-w: 330px;
    --toast-pad-x: 15px;
    --toast-pad-y: 13px;
    --toast-title-fs: 14px;
    --toast-msg-fs: 14px;
    --toast-icon: 32px;
    --toast-icon-box: 40px;
    --toast-bar-h: 4px;
  }
  .toast--lg{
    --toast-max-w: min(92vw, 460px);
    --toast-min-w: 340px;
    --toast-pad-x: 16px;
    --toast-pad-y: 14px;
    --toast-title-fs: 15px;
    --toast-msg-fs: 15px;
    --toast-icon: 34px;
    --toast-icon-box: 42px;
    --toast-bar-h: 4px;
  }
  .toast--xl{
    --toast-max-w: min(92vw, 520px);
    --toast-min-w: 360px;
    --toast-pad-x: 18px;
    --toast-pad-y: 16px;
    --toast-title-fs: 16px;
    --toast-msg-fs: 16px;
    --toast-icon: 38px;
    --toast-icon-box: 48px;
    --toast-bar-h: 5px;
  }

  @media (max-width: 420px){
    .toast-card{ min-width: calc(100vw - 28px); }
  }

  @media (prefers-reduced-motion: reduce){
    .toast-card{ transition:none; transform:none; }
    .toast-fill{ transition:none !important; }
  }
</style>

<div class="toast-overlay" aria-live="polite" aria-atomic="true"></div>

<script>
(function(){
  const DEFAULT_POSITION = 'tr';
  const FORCE_POSITION   = 'tr';
  const DEFAULT_SIZE     = 'xl';

  function ensurePos(position){
    const overlay = document.querySelector('.toast-overlay');
    if (!overlay) return null;

    let posEl = overlay.querySelector('.toast-pos');
    if (!posEl || !posEl.classList.contains(position)) {
      overlay.innerHTML = '';
      posEl = document.createElement('div');
      posEl.className = 'toast-pos ' + position;
      overlay.appendChild(posEl);
    }
    return { posEl };
  }

  function titleByType(type){
    switch(type){
      case 'success': return 'สำเร็จ';
      case 'error':   return 'เกิดข้อผิดพลาด';
      case 'warning': return 'โปรดตรวจสอบ';
      case 'info':    return 'แจ้งเตือน';
      default:        return 'แจ้งเตือน';
    }
  }

  function iconSvg(type){
    if (type === 'success') return `
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"></path>
      </svg>`;
    if (type === 'error') return `
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59Z"></path>
      </svg>`;
    if (type === 'warning') return `
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path>
      </svg>`;
    return `
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M11 17h2v-6h-2v6zm0-8h2V7h-2v2zm1-7C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
      </svg>`;
  }

  function showToast({type='info', message='', position=DEFAULT_POSITION, timeout=3800, size=DEFAULT_SIZE, title=null} = {}){
    type = (['success','info','warning','error'].includes(type) ? type : 'info');

    position = FORCE_POSITION || position || DEFAULT_POSITION;
    const allowedPos = ['tr','tl','br','bl'];
    if (!allowedPos.includes(position)) position = DEFAULT_POSITION;

    timeout = Number(timeout);
    if (!Number.isFinite(timeout) || timeout < 800) timeout = 3800;

    const s = (['sm','md','lg','xl'].includes(size) ? size : DEFAULT_SIZE);

    const ctx = ensurePos(position);
    if (!ctx) return;
    const { posEl } = ctx;

    const card = document.createElement('section');
    card.className = `toast-card toast--${s} toast--${type}`;
    card.setAttribute('role','status');

    const inner = document.createElement('div');
    inner.className = 'toast-inner';

    const ico = document.createElement('div');
    ico.className = 'toast-ico';
    ico.innerHTML = iconSvg(type);

    const text = document.createElement('div');
    text.className = 'toast-text';

    const h = document.createElement('div');
    h.className = 'toast-title';
    h.textContent = (title ?? titleByType(type));

    const p = document.createElement('p');
    p.className = 'toast-msg';
    p.textContent = message ?? '';

    text.append(h, p);

    const btn = document.createElement('button');
    btn.className = 'toast-close';
    btn.setAttribute('aria-label','Close');
    btn.innerHTML = '&times;';

    inner.append(ico, text, btn);

    const bar = document.createElement('div');
    bar.className = 'toast-bar';

    const fill = document.createElement('div');
    fill.className = 'toast-fill';
    bar.appendChild(fill);

    card.append(inner, bar);
    posEl.appendChild(card);

    requestAnimationFrame(() => {
      card.classList.add('show');
      requestAnimationFrame(() => {
        fill.style.transition = `width ${timeout}ms linear`;
        fill.style.width = '100%';
      });
    });

    let startAt = Date.now();
    let remain = timeout;

    function close(){
      card.classList.remove('show');
      setTimeout(()=> card.remove(), 180);
    }

    let timer = setTimeout(close, timeout + 60);
    btn.addEventListener('click', close);

    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); }, { once:true });

    card.addEventListener('mouseenter', () => {
      clearTimeout(timer);
      remain = Math.max(0, remain - (Date.now() - startAt));
      fill.style.transition = 'none';
      const doneRatio = 1 - (remain / timeout);
      fill.style.width = (doneRatio * 100) + '%';
    });

    card.addEventListener('mouseleave', () => {
      startAt = Date.now();
      fill.style.transition = `width ${remain}ms linear`;
      fill.style.width = '100%';
      timer = setTimeout(close, remain + 50);
    });
  }

  window.showToast = showToast;
  window.addEventListener('app:toast', e => showToast(e.detail || {}));

  @if ($type && $message)
  (function fireToast(){
    const payload = {
      type: @json($type),
      message: @json($message),
      position: @json($position ?? 'tr'),
      timeout: @json($timeout),
      size: @json($size ?? 'xl'),
    };

    function fire() {
      window.showToast(payload);
    }

    const needWait =
      document.documentElement.classList.contains('intro-pending') ||
      document.documentElement.classList.contains('intro-reveal');

    if (needWait) {
      window.addEventListener('introReveal:done', fire, { once: true });
    } else {
      fire();
    }
  })();
  @endif
})();
</script>
