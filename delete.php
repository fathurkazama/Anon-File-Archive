<?php
session_start();
if(!isset($_SESSION['admin'])) exit('Access denied');

if(!isset($_GET['index'])) exit('No file specified.');

$index = intval($_GET['index']);
$filesData = file_exists('files.json') ? json_decode(file_get_contents('files.json'), true) : [];

if(!isset($filesData[$index])) exit('File not found.');

$file = $filesData[$index];

// Hapus file utama
$filePath = 'uploads/'.$file['file'];
if(file_exists($filePath)) unlink($filePath);

// Hapus thumbnail
if(!empty($file['thumbnail'])){
    $thumbPath = 'thumbs/'.$file['thumbnail'];
    if(file_exists($thumbPath)) unlink($thumbPath);
}

// Hapus dari array dan simpan
array_splice($filesData, $index, 1);
file_put_contents('files.json', json_encode($filesData, JSON_PRETTY_PRINT));

header('Location: index.php');
?>
