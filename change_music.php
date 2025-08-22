<?php
session_start();
if(!isset($_SESSION['admin'])) exit('Access denied');

if(isset($_FILES['bgmusic'])){
    $file = $_FILES['bgmusic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if($ext != 'mp3') exit('Only MP3 allowed.');

    $musicDir = 'music/';
    if(!is_dir($musicDir)) mkdir($musicDir, 0755, true);

    $newName = time().'_'.basename($file['name']);
    if(move_uploaded_file($file['tmp_name'], $musicDir.$newName)){
        $settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
        $settings['background_music'] = $newName;
        file_put_contents('settings.json', json_encode($settings, JSON_PRETTY_PRINT));
        header('Location: index.php');
    } else {
        echo "Upload failed.";
    }
}
?>
