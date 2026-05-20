<?php
require_once __DIR__.'/../config/bootstrap.php';
$adminPageTitle='Live Chat'; $currentAdminPage='chat';

if($_SERVER['REQUEST_METHOD']==='POST'&&isAjax()){
    requireAdmin(); verifyCsrf();
    $action=$_POST['action']??'';
    $uid=(int)($_POST['user_id']??0);
    if($action==='send'){
        $msg=sanitize($_POST['message']??'');
        if(!$msg) jsonResponse(['success'=>false]);
        $stmt=db()->prepare("INSERT INTO live_chats (user_id,sender,message) VALUES (?,'admin',?)");
        $stmt->bind_param('is',$uid,$msg);$stmt->execute();$stmt->close();
        addNotification($uid,'Pesan dari Admin','Kamu mendapat pesan baru dari admin Noxara.','info');
        jsonResponse(['success'=>true]);
    }
    if($action==='load'){
        $chats=dbQuery("SELECT * FROM live_chats WHERE user_id=$uid ORDER BY created_at ASC LIMIT 50");
        $msgs=[];
        if($chats)while($c=$chats->fetch_assoc())$msgs[]=$c;
        dbQuery("UPDATE live_chats SET is_read=1 WHERE user_id=$uid AND sender='user'");
        jsonResponse(['success'=>true,'messages'=>$msgs]);
    }
    if($action==='broadcast'){
        $msg=sanitize($_POST['message']??'');
        broadcastNotification('Pengumuman Admin',$msg,'info');
        jsonResponse(['success'=>true,'message'=>'Broadcast terkirim ke semua user']);
    }
}

include __DIR__.'/_header.php';
$users=dbQuery("SELECT DISTINCT lc.user_id,u.username,u.full_name,(SELECT COUNT(*) FROM live_chats WHERE user_id=lc.user_id AND sender='user' AND is_read=0) as unread FROM live_chats lc JOIN users u ON lc.user_id=u.id GROUP BY lc.user_id ORDER BY MAX(lc.created_at) DESC");
?>
<div class="admin-page-header"><h1>Live Chat</h1><button class="btn btn-outline btn-sm" onclick="openBroadcast()"><i data-lucide="megaphone"></i> Broadcast</button></div>
<div class="admin-chat-layout">
  <div class="admin-chat-users" id="chatUserList">
    <?php if($users&&$users->num_rows>0):while($u=$users->fetch_assoc()):?>
    <div class="acu-item" onclick="loadChat(<?=$u['user_id']?>,'<?=addslashes($u['username'])?>')" data-uid="<?=$u['user_id']?>">
      <div class="acu-avatar"><?=strtoupper(substr($u['username'],0,1))?></div>
      <div class="acu-info"><div class="acu-name">@<?=htmlspecialchars($u['username'])?></div><div class="acu-full"><?=htmlspecialchars($u['full_name'])?></div></div>
      <?php if($u['unread']>0):?><span class="nav-badge"><?=$u['unread']?></span><?php endif;?>
    </div>
    <?php endwhile;else:?><div class="empty-state"><p>Belum ada chat</p></div><?php endif;?>
  </div>
  <div class="admin-chat-area" id="adminChatArea">
    <div class="empty-state"><i data-lucide="message-circle"></i><p>Pilih user untuk membalas chat</p></div>
  </div>
</div>

<!-- Broadcast Modal -->
<div class="modal-overlay" id="broadcastModal" style="display:none">
  <div class="modal-box"><div class="modal-title">Broadcast ke Semua User</div>
    <textarea id="broadcastMsg" class="form-input mt-2" rows="4" placeholder="Pesan broadcast..."></textarea>
    <div class="modal-actions mt-2">
      <button class="btn btn-outline" onclick="document.getElementById('broadcastModal').style.display='none'">Batal</button>
      <button class="btn btn-primary" onclick="sendBroadcast()"><i data-lucide="send"></i> Kirim</button>
    </div>
  </div>
</div>

<?php include __DIR__.'/_footer.php';?>
<script>
const CSRF='<?=csrfToken()?>'; let currentChatUid=null;
function loadChat(uid,username){
  currentChatUid=uid;
  document.querySelectorAll('.acu-item').forEach(e=>e.classList.remove('active'));
  document.querySelector(`.acu-item[data-uid="${uid}"]`)?.classList.add('active');
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=load&user_id=${uid}`})
  .then(r=>r.json()).then(d=>{
    const area=document.getElementById('adminChatArea');
    area.innerHTML=`<div class="achat-header"><strong>@${username}</strong></div><div class="achat-messages" id="achatMsgs"></div><div class="achat-input"><input type="text" id="achatInput" placeholder="Balas..." onkeydown="if(event.key==='Enter')sendAdminMsg()"><button onclick="sendAdminMsg()"><i data-lucide="send"></i></button></div>`;
    lucide.createIcons();
    const msgs=document.getElementById('achatMsgs');
    if(d.messages)d.messages.forEach(m=>{msgs.innerHTML+=`<div class="achat-msg ${m.sender==='admin'?'admin':'user'}"><div class="achat-bubble">${m.message}</div><div class="achat-time">${m.created_at}</div></div>`;});
    msgs.scrollTop=msgs.scrollHeight;
    document.querySelector(`.acu-item[data-uid="${uid}"] .nav-badge`)?.remove();
  });
}
function sendAdminMsg(){
  const msg=document.getElementById('achatInput')?.value.trim();
  if(!msg||!currentChatUid)return;
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=send&user_id=${currentChatUid}&message=${encodeURIComponent(msg)}`})
  .then(()=>{document.getElementById('achatInput').value='';loadChat(currentChatUid,document.querySelector(`.acu-item[data-uid="${currentChatUid}"] .acu-name`).textContent);});
}
function openBroadcast(){document.getElementById('broadcastModal').style.display='flex';}
function sendBroadcast(){
  const msg=document.getElementById('broadcastMsg').value.trim();
  if(!msg){showToast('Pesan kosong','error');return;}
  fetch(window.location.href,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body:`csrf_token=${CSRF}&action=broadcast&message=${encodeURIComponent(msg)}`})
  .then(r=>r.json()).then(d=>{document.getElementById('broadcastModal').style.display='none';showToast(d.message,'success');});
}
</script>
