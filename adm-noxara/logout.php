<?php
require_once __DIR__.'/../config/bootstrap.php';
unset($_SESSION['admin_id'],$_SESSION['admin_username']);
redirect(APP_URL.'/adm-noxara/login.php');
