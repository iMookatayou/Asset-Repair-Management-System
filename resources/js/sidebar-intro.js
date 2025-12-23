// resources/js/sidebar-intro.js
(() => {
  const NEXT = 'ui.sidebarIntro.next';
  if (sessionStorage.getItem(NEXT) !== '1') return;
  sessionStorage.removeItem(NEXT);

  /* ====== Tuning ====== */
  const HOLD_MS = 1700;        // ระยะเวลา spin
  const FLY_MS  = 1150;        // ระยะเวลาบิน
  const START_SCALE = 1.42;    // ขนาดตอนเริ่ม
  const PERSPECTIVE = 1100;
  const TILT_X = 7;
  const OVERLAY_FADE_MS = 220;

  const waitForLogo = (tries = 0) => {
    const logo =
      document.getElementById('sidebarLogo') ||
      document.querySelector('.sidebar-logo-img');

    if (!logo) {
      if (tries > 160) return;
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const rect = logo.getBoundingClientRect();
    // ต้องเช็คว่ามีขนาดจริงๆ แล้วหรือยัง
    if (!rect.width || !rect.height) {
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const prevVis = logo.style.visibility;
    logo.style.visibility = 'hidden';

    // 1. คำนวณจุดเริ่มต้น (กลางจอ) และจุดสิ้นสุด (ตำแหน่งโลโก้จริง) แบบ Pixel เป๊ะๆ
    const startX = (window.innerWidth / 2) - (rect.width / 2);
    const startY = (window.innerHeight / 2) - (rect.height / 2);
    const endX   = rect.left;
    const endY   = rect.top;

    // Overlay
    const overlay = document.createElement('div');
    overlay.style.cssText =
      'position:fixed;inset:0;z-index:1000000;' +
      'background:rgba(255,255,255,.62);backdrop-filter:blur(3px);' +
      'transition:opacity .35s ease;opacity:1;';
    document.body.appendChild(overlay);

    // Wrap: ใช้ left:0, top:0 แล้วคุมด้วย translate ทั้งหมด เพื่อความแม่นยำสูงสุด
    const wrap = document.createElement('div');
    wrap.style.cssText = [
      'position:fixed',
      'left:0',
      'top:0',
      `width:${rect.width}px`,
      `height:${rect.height}px`,
      `transform: translate3d(${startX}px, ${startY}px, 0)`, // เริ่มที่กลางจอ
      `perspective:${PERSPECTIVE}px`,
      'z-index:1000001',
      'pointer-events:none'
    ].join(';');
    document.body.appendChild(wrap);

    // Spinner
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
        'object-fit:contain', // ใช้ contain เพื่อให้สัดส่วนรูปไม่เพี้ยน
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
      // Fade in spinner
      spinner.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 200, fill: 'forwards' });

      // Step 1: Spin อยู่กับที่ (ที่กลางจอ)
      const spin = spinner.animate(
        [
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(0deg)` },
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(360deg)` },
        ],
        {
          duration: HOLD_MS,
          easing: 'cubic-bezier(.4, 0, .2, 1)',
          fill: 'forwards'
        }
      );
      try { await spin.finished; } catch (_) {}

      // Step 2: บินเข้าตำแหน่งจริง (Wrap เคลื่อนที่ X/Y)
      wrap.animate(
        [
          { transform: `translate3d(${startX}px, ${startY}px, 0)` },
          { transform: `translate3d(${endX}px, ${endY}px, 0)` }
        ],
        {
          duration: FLY_MS,
          easing: 'cubic-bezier(.22, .9, .22, 1)',
          fill: 'forwards'
        }
      );

      // Step 3: ลดขนาดและหมุนให้ตรง (Spinner ปรับ Scale/Rotation)
      const settle = spinner.animate(
        [
          { transform: `scale(${START_SCALE}) rotateX(${TILT_X}deg) rotateY(360deg)` },
          { transform: 'scale(1) rotateX(0deg) rotateY(360deg)' },
        ],
        {
          duration: FLY_MS,
          easing: 'cubic-bezier(.22, .9, .22, 1)',
          fill: 'forwards'
        }
      );

      settle.onfinish = () => {
        overlay.style.opacity = '0';
        // รอให้ fade overlay จบแล้วค่อยลบ DOM ทิ้ง จะได้เนียนที่สุด
        setTimeout(cleanup, OVERLAY_FADE_MS);
      };
    };

    const imgs = [front, back];
    const decodes = imgs.map(im => (im.decode ? im.decode().catch(() => {}) : Promise.resolve()));
    Promise.all(decodes).then(run);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => waitForLogo(), { once: true });
  } else {
    waitForLogo();
  }
})();
