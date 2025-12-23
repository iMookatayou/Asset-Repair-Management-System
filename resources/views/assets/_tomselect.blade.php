@once
  @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <style>
      /* (เอา style tomselect เดิมของคุณมาแปะตรงนี้ได้เลย) */
    </style>
  @endpush

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        function initTomSelectWithIcon(selector, placeholderText) {
          const el = document.querySelector(selector);
          if (!el) return;
          if (el.tomselect) return; // กัน init ซ้ำ

          const ts = new TomSelect(selector, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: placeholderText,
            searchField: ['text'],
          });

          const wrapper = ts.wrapper;
          if (!wrapper) return;
          wrapper.classList.add('ts-with-icon');

          const control = wrapper.querySelector('.ts-control');
          if (!control) return;

          if (!control.querySelector('.ts-select-icon')) {
            const icon = document.createElement('span');
            icon.className = 'ts-select-icon';
            icon.innerHTML = `
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="5" stroke="currentColor" stroke-width="2"></circle>
                <path d="M15 15l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            `;
            control.insertBefore(icon, control.firstChild);
          }
        }

        initTomSelectWithIcon('#category_id',   '— เลือกหมวดหมู่ —');
        initTomSelectWithIcon('#department_id', '— เลือกหน่วยงาน —');
        initTomSelectWithIcon('#status',        '— เลือกสถานะ —');
      });
    </script>
  @endpush
@endonce
