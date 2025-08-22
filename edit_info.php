<?php
session_start();
if(!isset($_SESSION['admin'])) die("Access denied");

$data = [
    "title"=>$_POST['title'] ?? '',
    "intro"=>$_POST['intro'] ?? '',
    "purpose"=>$_POST['purpose'] ?? '',
    "navigation"=>$_POST['navigation'] ?? '',
    "theme"=>$_POST['theme'] ?? ''
];

file_put_contents('info_content.json', json_encode($data, JSON_PRETTY_PRINT));
header("Location: info.php");
