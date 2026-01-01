// resources/js/sidebar-intro.js
(() => {
  const NEXT = 'ui.sidebarIntro.next';
  if (sessionStorage.getItem(NEXT) !== '1') return;
  sessionStorage.removeItem(NEXT);

  const HOLD_MS = 1700;
  const FLY_MS  = 1150;
  const START_SCALE = 1.42;
  const PERSPECTIVE = 1100;
  const TILT_X = 7;
  const OVERLAY_FADE_MS = 220;

  // Reveal tuning
  const LINE_DRAW_MS = 900;
  const STAGGER_MS   = 220;

  const q = (sel) => document.querySelector(sel);

  function forceRevealAll(reason = '') {
    const html = document.documentElement;

    // ปลด pending กัน lock ค้าง
    html.classList.remove('intro-pending');

    // เปิดทุกส่วนให้ชัวร์
    html.classList.add('intro-reveal', 'intro-show-nav', 'intro-show-side', 'intro-show-main', 'intro-show-footer');

    // แนะนำ: ล้างโหมด reveal ทิ้งหลังปลดทุกอย่างแล้ว เพื่อไม่ให้ CSS โหมดนี้ไปซ่อนอะไรค้าง
    setTimeout(() => {
      html.classList.remove('intro-reveal');
    }, 0);

    // ล้าง overlay/เส้นถ้ามี
    document.querySelectorAll('.intro-lines').forEach(el => el.remove());

    window.dispatchEvent(new CustomEvent('introReveal:done'));

    if (reason) console.warn('[intro] forceRevealAll:', reason);
  }

  function centerOf(el) {
    const r = el.getBoundingClientRect();
    return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
  }

  function safePointFor(selector, fallback) {
    const el = q(selector);
    if (!el) return fallback;
    const r = el.getBoundingClientRect();
    if (!r.width && !r.height) return fallback;
    return { x: r.left + Math.min(80, r.width * 0.35), y: r.top + Math.min(28, r.height * 0.25) };
  }

  function makeLineOverlay() {
    const wrap = document.createElement('div');
    wrap.className = 'intro-lines';
    wrap.innerHTML = `
      <svg viewBox="0 0 ${window.innerWidth} ${window.innerHeight}" preserveAspectRatio="none">
        <path id="introPath"></path>
        <circle class="dot" id="dotA" r="4"></circle>
        <circle class="dot" id="dotB" r="4"></circle>
        <circle class="dot" id="dotC" r="4"></circle>
        <circle class="dot" id="dotD" r="4"></circle>
      </svg>
    `;
    document.body.appendChild(wrap);
    return wrap;
  }

  function buildPath(points) {
    if (points.length < 2) return '';
    let d = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
      const p0 = points[i - 1];
      const p1 = points[i];
      const cx = (p0.x + p1.x) / 2;
      const cy = (p0.y + p1.y) / 2;
      d += ` Q ${cx} ${cy} ${p1.x} ${p1.y}`;
    }
    return d;
  }

  // Skip controller: คลิก/คีย์ข้าม intro ได้ทันที
  function setupSkip() {
    let done = false;
    const cancel = (reason) => {
      if (done) return;
      done = true;
      cleanup();
      forceRevealAll(reason || 'skip');
    };
    const onPointer = () => cancel('pointer-skip');
    const onKey = (e) => {
      if (e.key === 'Escape' || e.key === ' ' || e.key === 'Enter') cancel('key-skip');
    };
    const cleanup = () => {
      window.removeEventListener('pointerdown', onPointer);
      window.removeEventListener('keydown', onKey);
    };
    window.addEventListener('pointerdown', onPointer);
    window.addEventListener('keydown', onKey);
    return { cancel, cleanup, get done(){ return done; } };
  }

  async function revealSequenceFromLogo(logoEl, skipper) {
    const html = document.documentElement;

    // เปิด reveal mode (แต่ยังไม่โชว์ทุกอย่าง จนกว่าเราจะ add ทีละคลาส)
    html.classList.add('intro-reveal');
    html.classList.remove('intro-pending');

    // FIX: Navbar ของจริงคือ .navbar-pinwheel (เพิ่มเข้า selector)
    const A = centerOf(logoEl);
    const B = safePointFor('.navbar-pinwheel, .app-navbar, .navbar-hero', { x: A.x + 120, y: A.y - 80 });
    const C = safePointFor('#side', { x: A.x - 30,  y: A.y + 120 });
    const D = safePointFor('#main', { x: A.x + 240, y: A.y + 140 });

    const overlay = makeLineOverlay();
    const svg = overlay.querySelector('svg');
    const path = overlay.querySelector('#introPath');

    svg.setAttribute('viewBox', `0 0 ${window.innerWidth} ${window.innerHeight}`);

    const pts = [A, B, C, D];
    path.setAttribute('d', buildPath(pts));

    const dots = [
      overlay.querySelector('#dotA'),
      overlay.querySelector('#dotB'),
      overlay.querySelector('#dotC'),
      overlay.querySelector('#dotD')
    ];

    pts.forEach((p, i) => {
      const dot = dots[i];
      if (!dot) return;
      dot.setAttribute('cx', p.x);
      dot.setAttribute('cy', p.y);
      dot.style.opacity = i === 0 ? '1' : '0';
      dot.animate([{ opacity: dot.style.opacity }, { opacity: '1' }], { duration: 180, fill: 'forwards', delay: 80 + i * 120 });
    });

    const total = path.getTotalLength();
    path.style.strokeDasharray = String(total);
    path.style.strokeDashoffset = String(total);

    path.animate(
      [{ strokeDashoffset: total }, { strokeDashoffset: 0 }],
      { duration: LINE_DRAW_MS, easing: 'cubic-bezier(.22,.9,.22,1)', fill: 'forwards' }
    );

    const step = (cls, delay) => new Promise(res => {
      setTimeout(() => {
        if (skipper.done) return res();
        html.classList.add(cls);
        res();
      }, delay);
    });

    await step('intro-show-nav',    120);
    await step('intro-show-side',   STAGGER_MS);
    await step('intro-show-main',   STAGGER_MS);
    await step('intro-show-footer', STAGGER_MS);

    if (!skipper.done) {
      await new Promise(res => setTimeout(res, 220));
      overlay.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 260, fill: 'forwards' })
        .onfinish = () => overlay.remove();
    } else {
      overlay.remove();
    }

    // สำคัญ: กันค้าง — บังคับให้ครบทุกส่วน + ล้าง intro-reveal
    html.classList.add('intro-show-nav', 'intro-show-side', 'intro-show-main', 'intro-show-footer');
    setTimeout(() => {
      html.classList.remove('intro-reveal');
    }, 0);

    window.dispatchEvent(new CustomEvent('introReveal:done'));
  }

  const waitForLogo = (tries = 0) => {
    const logo =
      document.getElementById('sidebarLogo') ||
      document.querySelector('.sidebar-logo-img');

    if (!logo) {
      if (tries > 160) return forceRevealAll('logo-not-found');
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const rect = logo.getBoundingClientRect();
    if (!rect.width || !rect.height) {
      if (tries > 160) return forceRevealAll('logo-rect-not-ready');
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const prevVis = logo.style.visibility;
    logo.style.visibility = 'hidden';

    const startX = (window.innerWidth / 2) - (rect.width / 2);
    const startY = (window.innerHeight / 2) - (rect.height / 2);
    const endX   = rect.left;
    const endY   = rect.top;

    const overlay = document.createElement('div');
    overlay.style.cssText =
      'position:fixed;inset:0;z-index:1000000;' +
      'background:rgba(255,255,255,1);backdrop-filter:blur(3px);' +
      'transition:opacity .35s ease;opacity:1;';
    document.body.appendChild(overlay);

    const wrap = document.createElement('div');
    wrap.style.cssText = [
      'position:fixed',
      'left:0',
      'top:0',
      `width:${rect.width}px`,
      `height:${rect.height}px`,
      `transform: translate3d(${startX}px, ${startY}px, 0)`,
      `perspective:${PERSPECTIVE}px`,
      'z-index:1000001',
      'pointer-events:none'
    ].join(';');
    document.body.appendChild(wrap);

    const spinner = document.createElement('div');
    spinner.style.cssText = [
      `width:${rect.width}px`,
      `height:${rect.height}px`,
      'position:relative',
      'transform-style:preserve-3d',
      `transform:scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(0deg)`,
      'filter:drop-shadow(0 18px 40px rgba(0,0,0,.22))'
    ].join(';');
    wrap.appendChild(spinner);

    const makeFace = (rotateYdeg) => {
      const face = document.createElement('img');
      face.src = logo.currentSrc || logo.src;
      face.style.cssText = [
        'position:absolute;inset:0',
        'width:100%;height:100%',
        'object-fit:contain',
        'backface-visibility:hidden',
        `transform:rotateY(${rotateYdeg}deg)`
      ].join(';');
      return face;
    };

    const front = makeFace(0);
    const back  = makeFace(180);
    spinner.appendChild(front);
    spinner.appendChild(back);

    const cleanup = () => {
      wrap.remove();
      overlay.remove();
      logo.style.visibility = prevVis || 'visible';
    };

    const run = async () => {
      const skipper = setupSkip();

      spinner.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, fill: 'forwards' });

      const spin = spinner.animate(
        [
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(0deg)` },
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(360deg)` },
        ],
        { duration: HOLD_MS, easing: 'cubic-bezier(.4, 0, .2, 1)', fill: 'forwards' }
      );
      try { await spin.finished; } catch (_) {}

      if (skipper.done) return;

      wrap.animate(
        [
          { transform: `translate3d(${startX}px, ${startY}px, 0)` },
          { transform: `translate3d(${endX}px, ${endY}px, 0)` }
        ],
        { duration: FLY_MS, easing: 'cubic-bezier(.22, .9, .22, 1)', fill: 'forwards' }
      );

      const settle = spinner.animate(
        [
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(360deg)` },
          { transform: 'scale(1) rotateX(0deg) rotateY(360deg)' },
        ],
        { duration: FLY_MS, easing: 'cubic-bezier(.22, .9, .22, 1)', fill: 'forwards' }
      );

      settle.onfinish = () => {
        overlay.style.opacity = '0';
        setTimeout(async () => {
          cleanup();

          if (skipper.done) return;

          try {
            await revealSequenceFromLogo(logo, skipper);
          } catch (e) {
            console.error(e);
            forceRevealAll('revealSequence-error');
          } finally {
            skipper.cleanup?.();
          }
        }, OVERLAY_FADE_MS);
      };
    };

    Promise.all([front, back].map(im => (im.decode ? im.decode().catch(() => {}) : Promise.resolve())))
      .then(run)
      .catch((e) => {
        console.error(e);
        cleanup();
        forceRevealAll('logo-run-error');
      });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => waitForLogo(), { once: true });
  } else {
    waitForLogo();
  }
})();
