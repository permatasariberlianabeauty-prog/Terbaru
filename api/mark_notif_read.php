<?php
require_once __DIR__.'/../config/bootstrap.php';
requireLogin(); $uid=(int)currentUser()['id'];
verifyCsrf();
$data=json_decode(file_get_contents('php://input'),true);
if($data['all']??false){
    dbQuery("UPDATE notifications SET is_read=1 WHERE user_id=$uid OR is_broadcast=1");
}
jsonResponse(['success'=>true]);
