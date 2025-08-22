<?php
session_start();
if(!isset($_SESSION['admin'])){
    die("Access denied");
}

// Folder
$uploadDir = 'uploads/';
$thumbDir = 'thumbs/';
$allowedFileExt = ['txt','pdf','jpg','jpeg','png','gif','mp3','mp4','zip','rar']; // gaboleh php
$allowedThumbExt = ['jpg','jpeg','png','gif'];

// Ambil input
$file = $_FILES['file'] ?? null;
$thumbnail = $_FILES['thumbnail'] ?? null;
$description = $_POST['description'] ?? '';

if($file && $file['error'] == 0){
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext,$allowedFileExt)){
        die("File type not allowed");
    }

    $filename = time().'_'.$file['name'];
    move_uploaded_file($file['tmp_name'], $uploadDir.$filename);

    $thumbName = '';
    if($thumbnail && $thumbnail['error']==0){
        $tExt = strtolower(pathinfo($thumbnail['name'], PATHINFO_EXTENSION));
        if(in_array($tExt,$allowedThumbExt)){
            $thumbName = time().'_'.$thumbnail['name'];
            move_uploaded_file($thumbnail['tmp_name'], $thumbDir.$thumbName);
        }
    }

    $date = date('Y-m-d H:i:s');
    $fileData = ['file'=>$filename,'thumbnail'=>$thumbName,'description'=>$description,'date'=>$date];

    $files = file_exists('files.json') ? json_decode(file_get_contents('files.json'), true) : [];
    array_unshift($files,$fileData); // terbaru di atas
    file_put_contents('files.json', json_encode($files,JSON_PRETTY_PRINT));

    header('Location: index.php');
}else{
    die("No file uploaded");
}
