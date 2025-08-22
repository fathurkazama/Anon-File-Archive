<?php
session_start();
if(!isset($_SESSION['admin'])) exit;

if(isset($_POST['index'], $_POST['description'])){
    $index = intval($_POST['index']);
    $desc = trim($_POST['description']);
    
    $filesData = file_exists('files.json') ? json_decode(file_get_contents('files.json'), true) : [];
    if(isset($filesData[$index])){
        $filesData[$index]['description'] = $desc;
        file_put_contents('files.json', json_encode($filesData, JSON_PRETTY_PRINT));
        echo json_encode(['status'=>'success']);
        exit;
    }
}

echo json_encode(['status'=>'error']);
