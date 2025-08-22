<?php
session_start();
if(!isset($_SESSION['admin'])) exit('Access denied');

if($_SERVER['REQUEST_METHOD']=='POST'){
    $title = trim($_POST['title']);
    $about = trim($_POST['about']);
    $contact = trim($_POST['contact']);

    $data = [
        "title"=>$title,
        "about"=>$about,
        "contact"=>$contact
    ];

    file_put_contents('site_settings.json', json_encode($data, JSON_PRETTY_PRINT));
    header('Location: index.php'); // kembalikan ke index
}
