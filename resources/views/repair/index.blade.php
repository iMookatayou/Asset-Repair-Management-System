<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Asset Repair Management</title>
  <style>
    body{font-family:ui-sans-serif,system-ui; background:#0b0b0b; color:#e5e7eb; margin:0; padding:24px;}
    .container{max-width:980px; margin:0 auto;}
    h1{margin:0 0 16px}
    .card{background:#131313; border:1px solid #27272a; border-radius:16px; padding:16px; margin:16px 0}
    input,select,textarea{width:100%; padding:10px 12px; border:1px solid #30363d; border-radius:10px; background:#0f0f10; color:#e5e7eb}
    label{font-size:14px; color:#a1a1aa}
    .row{display:grid; grid-template-columns:1fr 1fr; gap:12px}
    button{padding:10px 14px; border-radius:10px; border:1px solid #3f3f46; background:#1f2937; color:#fff; cursor:pointer}
    table{width:100%; border-collapse:collapse; font-size:14px}
    th,td{border-bottom:1px solid #2a2a2a; padding:8px 10px; text-align:left}
    .actions{display:flex; gap:8px; flex-wrap:wrap}
    small{color:#9ca3af}
  </style>
</head>
<body>
<div class="container">
  <h1>📋 แจ้งซ่อมบำรุง</h1>

  <div class="card">
    <div class="row">
      <div>
        <label>Asset ID</label>
        <input id="asset_id" type="number" placeholder="เช่น 1">
      </div>
      <div>
        <label>Priority</label>
        <select id="priority">
          <option value="low">low</option>
          <option value="medium" selected>medium</option>
          <option value="high">high</option>
          <option value="urgent">urgent</option>
        </select>
      </div>
    </div>

    <div class="row" style="margin-top:12px">
      <div>
        <label>Title</label>
        <input id="title" type="text" placeholder="อาการ/ปัญหา">
      </div>
      <div>
        <label>Reporter ID</label>
        <input id="reporter_id" type="number" placeholder="เช่น 2">
      </div>
    </div>

    <div style="margin-top:12px">
      <label>Description</label>
      <textarea id="description" rows="3" placeholder="รายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
    </div>

    <div class="actions" style="margin-top:12px">
      <button onclick="createRequest()">สร้างคำขอซ่อม</button>
      <button onclick="loadRequests()">โหลดรายการล่าสุด</button>
      <small>หมายเหตุ: API ของคุณติด <code>auth:sanctum</code> ถ้ายังไม่ได้ล็อกอิน ให้ใช้โหมด Token ชั่วคราวด้านล่าง</small>
    </div>
  </div>

  <div class="card">
    <div class="row">
      <div>
        <label>Bearer Token (ชั่วคราวสำหรับทดสอบ)</label>
        <input id="token" type="text" placeholder="ใส่ Personal Access Token ถ้ามี">
      </div>
      <div>
        <label>API Base</label>
        <input id="baseUrl" type="text" value="/api">
      </div>
    </div>
    <small>ถ้าใช้ Sanctum session (Breeze) และอยู่โดเมนเดียวกัน สามารถเว้นว่างช่อง Token แล้วเปิดโหมด Session ด้านล่าง</small>
    <div class="actions" style="margin-top:8px">
      <button onclick="useSession=true; alert('ใช้โหมด Session แล้ว');">ใช้โหมด Session (Sanctum)</button>
      <button onclick="useSession=false; alert('ใช้โหมด Bearer Token แล้ว');">ใช้โหมด Bearer Token</button>
    </div>
  </div>

  <div class="card">
    <h3>รายการคำขอซ่อม</h3>
    <table id="list">
      <thead><tr><th>ID</th><th>Asset</th><th>Title</th><th>Status</th><th>Priority</th><th>วันที่แจ้ง</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
  let useSession = false; // เปลี่ยนเป็น true ถ้า login ผ่าน Breeze ในโดเมนเดียวกัน

  async function api(path, options = {}) {
    const base = document.getElementById('baseUrl').value || '/api';
    const url = base.replace(/\/$/, '') + path;

    const headers = options.headers || {};
    headers['Content-Type'] = 'application/json';

    if (!useSession) {
      const token = document.getElementById('token').value.trim();
      if (token) headers['Authorization'] = 'Bearer ' + token;
    } else {
      options.credentials = 'include';
      // ถ้ายังไม่ได้ init CSRF (สำหรับ Sanctum), เปิดบรรทัดนี้ครั้งแรก:
      // await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
    }

    return fetch(url, { ...options, headers });
  }

  async function loadRequests() {
    const res = await api('/repair-requests?per_page=10');
    const data = await res.json();
    const rows = (data.data || []).map(r => `
      <tr>
        <td>${r.id}</td>
        <td>${r.asset_id}</td>
        <td>${escapeHtml(r.title || '')}</td>
        <td>${r.status}</td>
        <td>${r.priority}</td>
        <td>${r.request_date ?? '-'}</td>
      </tr>
    `).join('');
    document.querySelector('#list tbody').innerHTML = rows || '<tr><td colspan="6"><small>ไม่มีข้อมูล</small></td></tr>';
  }

  async function createRequest() {
    const payload = {
      asset_id:    Number(document.getElementById('asset_id').value),
      reporter_id: Number(document.getElementById('reporter_id').value),
      title:       document.getElementById('title').value,
      description: document.getElementById('description').value,
      priority:    document.getElementById('priority').value,
    };

    const res = await api('/repair-requests', { method: 'POST', body: JSON.stringify(payload) });
    const data = await res.json();
    if (!res.ok) {
      alert('Error: ' + (data.message || JSON.stringify(data)));
      return;
    }
    alert('สร้างสำเร็จ ID: ' + (data.data?.id || ''));
    loadRequests();
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }

  // โหลดรอบแรก
  loadRequests();
</script>
</body>
</html>
