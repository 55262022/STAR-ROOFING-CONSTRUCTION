<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Basic pagination / search kept but primary UI is a two-column inbox+chat
$search_term = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Helper: ensure replies table exists and is_accepted column exists (attempt, but won't break if fails)
$conn->query("
    CREATE TABLE IF NOT EXISTS replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        inquiry_id INT NOT NULL,
        sender ENUM('admin','client') NOT NULL,
        message TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
");

if (!$conn->query("SHOW COLUMNS FROM inquiries LIKE 'is_accepted'")->fetch_assoc()) {
    // try to add column; if fails (permissions) it's ok — accept endpoint also attempts
    @$conn->query("ALTER TABLE inquiries ADD COLUMN is_accepted TINYINT(1) DEFAULT 0");
}

// We'll fetch initial page load data server-side for SEO and fallback
$inquiries = [];
$sql = "SELECT id, firstname, lastname, email, phone, inquiry_type, message, submitted_at, COALESCE(is_accepted,0) as is_accepted
        FROM inquiries
        WHERE COALESCE(is_accepted,0) = 0
        ORDER BY submitted_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();
$inquiries = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin — Inquiries / Messages — Star Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root{
  --bg:#f4f7fb;
  --card:#fff;
  --accent:#2f80ed; /* blue */
  --muted:#7a869a;
  --success:#1abc9c;
  --danger:#e74c3c;
  font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
  color: #213040;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);min-height:100vh}
.main-container{display:flex;gap:24px;padding:20px;max-width:1200px;margin:0 auto}
.sidebar{
  width:360px; min-width:280px;
  display:flex;flex-direction:column;gap:12px;
}
.brand{
  background:var(--card);padding:14px;border-radius:12px;display:flex;align-items:center;gap:12px;box-shadow:0 6px 18px rgba(20,30,60,0.06)
}
.brand h2{margin:0;font-size:18px}
.controls{
  display:flex;gap:8px;align-items:center;padding:12px;background:var(--card);border-radius:12px;box-shadow:0 6px 18px rgba(20,30,60,0.03)
}
.controls input[type="search"]{
  flex:1;padding:10px 12px;border-radius:8px;border:1px solid #e6eef9;font-size:14px
}
.controls button{background:var(--accent);color:#fff;border:none;padding:10px 12px;border-radius:8px;cursor:pointer}
.list-card{background:var(--card);padding:8px;border-radius:12px;box-shadow:0 6px 18px rgba(20,30,60,0.04);overflow:auto;max-height:68vh}
.inquiry-item{display:flex;gap:12px;padding:10px;border-radius:8px;align-items:flex-start;border:1px solid transparent;cursor:pointer}
.inquiry-item:hover{background:#f6fbff;border-color:#e6f0ff}
.inquiry-meta{flex:1;min-width:0}
.inquiry-title{font-weight:600;margin:0;font-size:15px}
.inquiry-sub{color:var(--muted);font-size:13px;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inquiry-time{font-size:12px;color:var(--muted);min-width:80px;text-align:right}

.badge-new{background:#eef6ff;color:var(--accent);padding:4px 8px;border-radius:12px;font-size:12px;font-weight:600}

.accept-btn{background:#fff;border:1px solid #e6eef9;padding:8px 10px;border-radius:8px;cursor:pointer;font-weight:600}

.chat-area{flex:1;display:flex;flex-direction:column;gap:12px}
.chat-header{background:var(--card);padding:14px;border-radius:12px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 6px 18px rgba(20,30,60,0.04)}
.chat-body{flex:1;background:var(--card);padding:14px;border-radius:12px;overflow:auto;max-height:68vh;box-shadow:0 6px 18px rgba(20,30,60,0.04)}
.chat-message{max-width:75%;padding:10px 12px;border-radius:10px;margin:8px 0;line-height:1.4}
.msg-admin{background:#e8f4fd;border:1px solid #d7edff;margin-left:auto}
.msg-client{background:#f3f5f7;border:1px solid #e6e9ee;margin-right:auto}
.chat-footer{display:flex;gap:10px;align-items:center}
.chat-footer textarea{flex:1;padding:12px;border-radius:10px;border:1px solid #e6eef9;min-height:56px;resize:vertical}
.send-btn{background:var(--accent);color:#fff;border:none;padding:12px 16px;border-radius:10px;cursor:pointer;font-weight:700}
.small-muted{font-size:13px;color:var(--muted)}
.empty-state{padding:30px;text-align:center;color:var(--muted);background:var(--card);border-radius:12px}
@media (max-width:900px){
  .main-container{flex-direction:column;padding:12px}
  .sidebar{width:auto}
}
</style>
</head>
<body>
<div class="main-container">
  <div class="sidebar">
    <div class="brand">
      <img src="../assets/logo.png" alt="" style="width:44px;height:44px;border-radius:6px;object-fit:cover">
      <div>
        <h2>Star Roofing — Inquiries</h2>
        <div class="small-muted">Admin panel — Manage & reply</div>
      </div>
    </div>

    <div class="controls">
      <form id="searchForm" style="display:flex;gap:8px;flex:1">
        <input id="searchInput" type="search" name="search" placeholder="Search name, email or message..." value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
      <button id="refreshBtn" title="Refresh"><i class="fas fa-sync"></i></button>
    </div>

    <div class="list-card" id="inquiriesList" aria-live="polite">
      <!-- items loaded by JS -->
      <?php foreach ($inquiries as $inq): ?>
        <div class="inquiry-item" data-id="<?= $inq['id'] ?>">
          <div class="inquiry-meta">
            <div style="display:flex;gap:8px;align-items:center">
              <div class="inquiry-title"><?= htmlspecialchars($inq['firstname'] . ' ' . $inq['lastname']) ?></div>
              <div style="margin-left:auto;font-size:12px;color:var(--muted)"><?= htmlspecialchars($inq['inquiry_type']) ?></div>
            </div>
            <div class="inquiry-sub">
              <?= htmlspecialchars(mb_strimwidth($inq['message'],0,80,'...')) ?>
            </div>
            <div class="small-muted" style="margin-top:8px"><?= htmlspecialchars($inq['email']) ?> · <?= htmlspecialchars($inq['phone']) ?></div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
            <div class="inquiry-time"><?= date('M j, g:ia', strtotime($inq['submitted_at'])) ?></div>
            <button class="accept-btn" onclick="event.stopPropagation(); acceptInquiry(<?= $inq['id'] ?>)">Accept</button>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (count($inquiries) === 0): ?>
        <div class="empty-state">No new inquiries. They'll show here when clients submit them.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="chat-area">
    <div class="chat-header">
      <div>
        <div id="chatTitle"><strong>Select an inquiry to start</strong></div>
        <div class="small-muted" id="chatSubtitle">Accepted inquiries will open a conversation here.</div>
      </div>
      <div>
        <button id="closeThreadBtn" style="display:none;background:#fff;border:1px solid #e6eef9;padding:8px 10px;border-radius:8px">Close</button>
      </div>
    </div>

    <div class="chat-body" id="chatBody">
      <div class="empty-state">Open an accepted inquiry to view and reply to messages.</div>
    </div>

    <div class="chat-footer" id="chatFooter" style="display:none">
      <textarea id="replyInput" placeholder="Write your reply..."></textarea>
      <button class="send-btn" id="sendBtn">Send</button>
    </div>
  </div>
</div>

<script>

const API = {
  fetchInquiries: 'messages-API/fetch_inquiries.php',
  acceptInquiry: 'messages-API/accept_inquiry.php',
  fetchThread: 'messages-API/fetch_thread.php',
  sendReply: 'messages-API/send_reply.php'
};

let currentThreadId = null;
let pollingThread = null;
let pollingList = null;

// ---------- Helpers ----------
function dom(q, ctx=document){ return ctx.querySelector(q) }
function domAll(q, ctx=document){ return Array.from(ctx.querySelectorAll(q)) }

// ---------- Load & Poll Inquiries List ----------
async function loadInquiries() {
  try {
    const res = await fetch(API.fetchInquiries + '?search=' + encodeURIComponent(dom('#searchInput').value||''));
    const js = await res.json();
    const list = dom('#inquiriesList');
    list.innerHTML = '';
    if (!Array.isArray(js) || js.length === 0) {
      list.innerHTML = '<div class="empty-state">No new inquiries.</div>';
      return;
    }
    for (const i of js) {
      const item = document.createElement('div');
      item.className = 'inquiry-item';
      item.dataset.id = i.id;
      item.innerHTML = `
        <div class="inquiry-meta">
          <div style="display:flex;gap:8px;align-items:center">
            <div class="inquiry-title">${escapeHtml(i.firstname + ' ' + i.lastname)}</div>
            <div style="margin-left:auto;font-size:12px;color:var(--muted)">${escapeHtml(i.inquiry_type)}</div>
          </div>
          <div class="inquiry-sub">${escapeHtml(trim(i.message,80))}</div>
          <div class="small-muted" style="margin-top:8px">${escapeHtml(i.email)} · ${escapeHtml(i.phone || '')}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
          <div class="inquiry-time">${formatDate(i.submitted_at)}</div>
          <button class="accept-btn" onclick="event.stopPropagation(); acceptInquiry(${i.id})">Accept</button>
        </div>
      `;
      item.addEventListener('click', () => openThread(i.id));
      list.appendChild(item);
    }
  } catch (err) {
    console.error('loadInquiries error', err);
  }
}

function startListPolling(){
  if (pollingList) clearInterval(pollingList);
  pollingList = setInterval(loadInquiries, 3000); // every 3s
  loadInquiries();
}

// ---------- Accept Inquiry ----------
async function acceptInquiry(id){
  try {
    const res = await fetch(API.acceptInquiry, {
      method:'POST',
      body: new URLSearchParams({id})
    });
    const j = await res.json();
    if (j.success) {
      // remove item from list UI and open thread
      // refresh list immediately
      await loadInquiries();
      openThread(id);
      Swal.fire({icon:'success',title:'Accepted',text:'Inquiry accepted. Conversation opened.'});
    } else {
      Swal.fire({icon:'error',title:'Error',text:j.message || 'Could not accept inquiry.'});
    }
  } catch(e){
    Swal.fire({icon:'error',title:'Error',text:'Network error.'});
  }
}

// ---------- Thread (chat) ----------
async function openThread(id){
  currentThreadId = id;
  dom('#chatFooter').style.display = 'flex';
  dom('#closeThreadBtn').style.display = 'inline-block';
  dom('#chatTitle').innerHTML = '<strong>Conversation</strong>';
  dom('#chatSubtitle').textContent = 'Loading messages…';
  // load immediately then start polling
  await loadThread();
  if (pollingThread) clearInterval(pollingThread);
  pollingThread = setInterval(loadThread, 2000); // every 2s for real-time feel
}

async function loadThread(){
  if (!currentThreadId) return;
  try {
    const res = await fetch(API.fetchThread + '?id=' + encodeURIComponent(currentThreadId));
    const j = await res.json();
    if (!j || !j.inquiry) return;
    // render
    const body = dom('#chatBody');
    body.innerHTML = '';
    // inquiry as first message (client)
    const inq = j.inquiry;
    const inqNode = document.createElement('div');
    inqNode.className = 'chat-message msg-client';
    inqNode.innerHTML = `<strong>${escapeHtml(inq.firstname + ' ' + inq.lastname)}</strong><br>
                         <small class="small-muted">${formatDate(inq.submitted_at)}</small><div style="margin-top:8px">${nl2br(escapeHtml(inq.message))}</div>`;
    body.appendChild(inqNode);
    // replies
    for (const r of j.replies) {
      const el = document.createElement('div');
      el.className = 'chat-message ' + (r.sender === 'admin' ? 'msg-admin' : 'msg-client');
      el.innerHTML = `<small class="small-muted">${r.sender === 'admin' ? 'You' : 'Client'} • ${formatDate(r.sent_at)}</small>
                      <div style="margin-top:6px">${nl2br(escapeHtml(r.message))}</div>`;
      body.appendChild(el);
    }
    // scroll to bottom
    body.scrollTop = body.scrollHeight;
    dom('#chatSubtitle').textContent = `${inq.email} · ${inq.phone || 'no phone'}`;
  } catch (err) {
    console.error('loadThread err', err);
  }
}

// ---------- Send reply ----------
async function sendReply(){
  const txt = dom('#replyInput').value.trim();
  if (!currentThreadId || txt.length === 0) return;
  try {
    const res = await fetch(API.sendReply, {
      method:'POST',
      body: new URLSearchParams({inquiry_id: currentThreadId, message: txt})
    });
    const j = await res.json();
    if (j.success) {
      dom('#replyInput').value = '';
      await loadThread();
    } else {
      Swal.fire({icon:'error',title:'Error',text:j.message || 'Could not send reply.'});
    }
  } catch (err) {
    Swal.fire({icon:'error',title:'Error',text:'Network error.'});
  }
}

function closeThread(){
  currentThreadId = null;
  dom('#chatBody').innerHTML = '<div class="empty-state">Open an accepted inquiry to view and reply to messages.</div>';
  dom('#chatFooter').style.display = 'none';
  dom('#closeThreadBtn').style.display = 'none';
  if (pollingThread) clearInterval(pollingThread);
}

// ---------- Utilities ----------
function nl2br(s){ return s.replace(/\n/g,'<br>') }
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"'`]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[c]) }
function trim(s,n){ if(!s) return ''; return s.length>n? s.substr(0,n-1)+'…':s }
function formatDate(d){ if(!d) return ''; const dt=new Date(d); return dt.toLocaleString(); }

// ---------- Bindings ----------
dom('#searchForm').addEventListener('submit', (e)=>{
  e.preventDefault();
  loadInquiries();
});
dom('#refreshBtn').addEventListener('click', ()=> loadInquiries());
dom('#sendBtn').addEventListener('click', sendReply);
dom('#closeThreadBtn').addEventListener('click', closeThread);
dom('#replyInput').addEventListener('keypress', function(e){ if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendReply(); } });

// init
startListPolling();
</script>
</body>
</html>
