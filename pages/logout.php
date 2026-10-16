<?php
require_once __DIR__ . '/../includes/functions.php';
session_destroy();
session_start();
setFlash('success','You have been logged out successfully.');
header('Location: '.BASE_URL.'/index.php'); exit;
