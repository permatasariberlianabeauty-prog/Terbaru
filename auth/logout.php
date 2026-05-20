<?php
require_once __DIR__ . '/../config/bootstrap.php';
session_destroy();
header('Location: ' . APP_URL . '/auth/login.php');
exit;
