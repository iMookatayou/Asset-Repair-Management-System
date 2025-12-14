// resources/js/sidebar-intro.js
(() => {
  const NEXT = 'ui.sidebarIntro.next';
  if (sessionStorage.getItem(NEXT) !== '1') return;
  sessionStorage.removeItem(NEXT);

  /* ====== Tuning ====== */
  const HOLD_MS = 1700;        // ระยะเวลา spin 1 รอบ
  const FLY_MS  = 1150;        // ระยะเวลาบินกลับ sidebar
  const START_SCALE = 1.42;    // ขนาดตอนเปิดตัว
  const PERSPECTIVE = 1100;    // ความลึก 3D
  const TILT_X = 7;            // เอียงนิดๆ ให้ดูแพง
  const OVERLAY_FADE_MS = 220; // fade ออกตอนจบ

  const waitForLogo = (tries = 0) => {
    const logo =
      document.getElementById('sidebarLogo') ||
      document.querySelector('.sidebar-logo-img');

    if (!logo) {
      if (tries > 160) return;
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const rect = logo.getBoundingClientRect();
    if (!rect.width || !rect.height) {
      return setTimeout(() => waitForLogo(tries + 1), 50);
    }

    const prevVis = logo.style.visibility;
    logo.style.visibility = 'hidden';

    // Overlay
    const overlay = document.createElement('div');
    overlay.style.cssText =
      'position:fixed;inset:0;z-index:1000000;' +
      'background:rgba(255,255,255,.62);backdrop-filter:blur(3px);' +
      'transition:opacity .35s ease;opacity:1;';
    document.body.appendChild(overlay);

    // Wrap (ตำแหน่ง + perspective)
    const wrap = document.createElement('div');
    wrap.style.cssText = [
      'position:fixed',
      'left:50%',
      'top:50%',
      'transform:translate(-50%,-50%)',
      `width:${rect.width}px`,
      `height:${rect.height}px`,
      `perspective:${PERSPECTIVE}px`,
      'z-index:1000001',
      'pointer-events:none'
    ].join(';');
    document.body.appendChild(wrap);

    // Spinner (ตัวที่หมุนจริง) — ใช้ 2 หน้า กัน “หายตอนหมุน”
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

    // สร้างหน้า front/back
    const makeFace = (rotateYdeg) => {
      const face = document.createElement('img');
      face.src = logo.currentSrc || logo.src;
      face.alt = 'intro-logo';
      face.style.cssText = [
        'position:absolute',
        'inset:0',
        `width:${rect.width}px`,
        `height:${rect.height}px`,
        'object-fit:contain',
        'display:block',
        'backface-visibility:hidden',
        `transform:rotateY(${rotateYdeg}deg)`
      ].join(';');
      return face;
    };

    const front = makeFace(0);
    const back  = makeFace(180);
    spinner.appendChild(front);
    spinner.appendChild(back);

    const dx = (rect.left + rect.width / 2) - (window.innerWidth / 2);
    const dy = (rect.top + rect.height / 2) - (window.innerHeight / 2);

    const cleanup = () => {
      wrap.remove();
      overlay.remove();
      logo.style.visibility = prevVis || 'visible';
    };

    const run = async () => {
      // fade in (กันกระพริบ)
      spinner.animate([{ opacity: 0 }, { opacity: 1 }], {
        duration: 200,
        easing: 'ease-out',
        fill: 'forwards'
      });

      // 1) Spin 1 รอบเต็ม (360deg) — ไม่หาย เพราะมี 2 หน้า
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

      // 2) บินกลับ sidebar (คง orientation ให้ดูเนียน) + ลด scale เข้าที่
      wrap.animate(
        [
          { transform: 'translate(-50%,-50%)' },
          { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px))` }
        ],
        {
          duration: FLY_MS,
          easing: 'cubic-bezier(.22, .9, .22, 1)',
          fill: 'forwards'
        }
      );

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
        setTimeout(cleanup, OVERLAY_FADE_MS);
      };
    };

    // รอรูป “พร้อมจริง” ก่อนค่อยเริ่ม
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
