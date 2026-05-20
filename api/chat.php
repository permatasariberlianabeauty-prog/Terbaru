<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $uid=(int)currentUser()['id'];
$action=$_POST['action']??$_GET['action']??'';

if($action==='send'&&$_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();
    $msg=sanitize($_POST['message']??'');
    if(!$msg) jsonResponse(['success'=>false,'message'=>'Pesan kosong']);
    $stmt=db()->prepare("INSERT INTO live_chats (user_id,sender,message) VALUES (?,'user',?)");
    $stmt->bind_param('is',$uid,$msg);$stmt->execute();$stmt->close();
    jsonResponse(['success'=>true,'id'=>db()->insert_id]);
}

if($action==='load'){
    $chats=dbQuery("SELECT * FROM live_chats WHERE user_id=$uid ORDER BY created_at ASC LIMIT 50");
    $msgs=[];
    if($chats)while($c=$chats->fetch_assoc())$msgs[]=$c;
    dbQuery("UPDATE live_chats SET is_read=1 WHERE user_id=$uid AND sender='admin'");
    jsonResponse(['success'=>true,'messages'=>$msgs]);
}

if($action==='rate'&&$_SERVER['REQUEST_METHOD']==='POST'){
    verifyCsrf();
    $rating=(int)($_POST['rating']??5);
    $stmt=db()->prepare("INSERT INTO chat_ratings (user_id,rating) VALUES (?,?)");
    $stmt->bind_param('ii',$uid,$rating);$stmt->execute();$stmt->close();
    jsonResponse(['success'=>true]);
}
jsonResponse(['success'=>false,'message'=>'Invalid action']);
