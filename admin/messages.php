<?php
include '../authentication/auth.php';
require_once '../database/starroofing_db.php';

// Basic pagination/search
$search_term = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Ensure replies table exists
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

// Fetch initial inquiries for server-side render
$inquiries = [];
$sql = "SELECT id, firstname, lastname, email, phone, message, submitted_at
        FROM inquiries
        WHERE COALESCE(is_accepted,0) = 0
        ORDER BY submitted_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii',$limit,$offset);
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
<style>
:root{
  --bg:#f4f7fb;
  --card:#fff;
  --accent:#2f80ed;
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
      <?php foreach ($inquiries as $inq): ?>
        <div class="inquiry-item" data-id="<?= $inq['id'] ?>">
          <div class="inquiry-meta">
            <div class="inquiry-title"><?= htmlspecialchars($inq['firstname'] . ' ' . $inq['lastname']) ?></div>
            <div class="inquiry-sub"><?= htmlspecialchars(mb_strimwidth($inq['message'],0,80,'...')) ?></div>
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
let currentThreadId = null;

// ---------- Helpers ----------
const dom = (q,ctx=document)=>ctx.querySelector(q);
const escapeHtml = s=>s? String(s).replace(/[&<>"'`]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;',"`":'&#96;'})[c]):'';
const nl2br = s=>s.replace(/\n/g,'<br>');
const formatDate = d=>d? new Date(d).toLocaleString():'';
const trim = (s,n)=>s.length>n? s.substr(0,n-1)+'…':s;

// ---------- Load Inquiries ----------
async function loadInquiries(){
  try {
    const searchTerm = encodeURIComponent(dom('#searchInput').value||'');
    const res = await fetch(`messages-API/fetch_inquiries.php?search=${searchTerm}`);
    const data = await res.json();

    const list = dom('#inquiriesList');
    list.innerHTML = '';

    if(!data.success || !data.inquiries || data.inquiries.length === 0){
      list.innerHTML = '<div class="empty-state">No new inquiries.</div>';
      return;
    }

    data.inquiries.forEach(i => {
      const item = document.createElement('div');
      item.className = 'inquiry-item';
      item.dataset.id = i.id;
      item.innerHTML = `
        <div class="inquiry-meta">
          <div class="inquiry-title">${escapeHtml(i.firstname+' '+i.lastname)}</div>
          <div class="inquiry-sub">${escapeHtml(trim(i.message,80))}</div>
          <div class="small-muted" style="margin-top:8px">${escapeHtml(i.email)} · ${escapeHtml(i.phone||'')}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
          <div class="inquiry-time">${formatDate(i.submitted_at)}</div>
          <button class="accept-btn" onclick="event.stopPropagation(); acceptInquiry(${i.id})">Accept</button>
        </div>
      `;
      item.addEventListener('click',()=>openThread(i.id));
      list.appendChild(item);
    });

  } catch(e){
    console.error('loadInquiries error', e);
  }
}

// ---------- Accept Inquiry ----------
async function acceptInquiry(id){
  try {
    const res = await fetch('messages-API/accept_inquiry.php', {
      method:'POST',
      body:new URLSearchParams({id})
    });
    const j = await res.json();
    if(j.success){
      await loadInquiries();
      openThread(id);
      alert('Inquiry accepted. Conversation opened.');
    } else {
      alert(j.message || 'Could not accept inquiry.');
    }
  } catch(e){
    alert('Network error.');
  }
}

// ---------- Load Thread ----------
async function openThread(id){
  currentThreadId = id;
  dom('#chatFooter').style.display='flex';
  dom('#closeThreadBtn').style.display='inline-block';
  dom('#chatTitle').innerHTML='<strong>Conversation</strong>';
  dom('#chatSubtitle').textContent='Loading messages…';
  await loadThread();
}

async function loadThread(){
  if(!currentThreadId) return;
  try {
    const res = await fetch(`messages-API/fetch_thread.php?id=${currentThreadId}`);
    const data = await res.json();

    const body = dom('#chatBody');
    body.innerHTML='';

    if(!data.success || !data.inquiry){
      body.innerHTML='<div class="empty-state">No messages yet.</div>';
      return;
    }

    const allMessages = [
      {sender:'client', message:data.inquiry.message, created_at:data.inquiry.submitted_at, author:data.inquiry.firstname+' '+data.inquiry.lastname},
      ...data.replies.map(r=>({sender:r.sender, message:r.message, created_at:r.sent_at, author:r.sender==='admin'?'Admin':'Client'}))
    ];

    allMessages.forEach(msg=>{
      const node = document.createElement('div');
      node.className='chat-message '+(msg.sender==='admin'?'msg-admin':'msg-client');
      node.innerHTML=`<div class="chat-author">${escapeHtml(msg.author)}</div>
                      <div class="chat-text">${nl2br(escapeHtml(msg.message))}</div>
                      <div class="chat-time">${formatDate(msg.created_at)}</div>`;
      body.appendChild(node);
    });

    body.scrollTop = body.scrollHeight;
    dom('#chatSubtitle').textContent='Conversation loaded';

  } catch(e){
    console.error('loadThread error', e);
  }
}

// ---------- Send Reply ----------
dom('#sendBtn').addEventListener('click', async ()=>{
  const msg = dom('#replyInput').value.trim();
  if(!msg || !currentThreadId) return;
  dom('#sendBtn').disabled=true;

  try {
    const res = await fetch('messages-API/send_reply.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({inquiry_id:currentThreadId,message:msg})
    });
    const j = await res.json();
    if(j.success){
      dom('#replyInput').value='';
      await loadThread();
    } else {
      alert(j.message || 'Could not send reply.');
    }
  } catch(e){
    alert('Network error.');
  } finally {
    dom('#sendBtn').disabled=false;
  }
});

// ---------- Close Thread ----------
dom('#closeThreadBtn').addEventListener('click',()=>{
  currentThreadId=null;
  dom('#chatBody').innerHTML='<div class="empty-state">Open an accepted inquiry to view and reply to messages.</div>';
  dom('#chatFooter').style.display='none';
  dom('#closeThreadBtn').style.display='none';
  dom('#chatTitle').innerHTML='<strong>Select an inquiry to start</strong>';
  dom('#chatSubtitle').textContent='Accepted inquiries will open a conversation here.';
});

// ---------- Search & Refresh ----------
dom('#searchForm').addEventListener('submit',e=>{e.preventDefault(); loadInquiries();});
dom('#refreshBtn').addEventListener('click',()=>loadInquiries());

// ---------- Init ----------
loadInquiries();
setInterval(loadInquiries,5000); // optional polling

</script>
</body>
</html>