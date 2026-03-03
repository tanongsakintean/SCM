<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_GET['type']='combined';
$_GET['start_date']='2024-01-01';
$_GET['end_date']='2026-12-31';

$_SESSION['user_id']=2;
$_SESSION['role_id']=1; // Assume admin role ID is 1

include 'report_api.php';
?>
