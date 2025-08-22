<?php
session_start();

if(isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['role']) || ($_SESSION['role']!=="operator" && $_SESSION['role']!=="admin")){
    header("Location: login.php");
    exit;
}

$filesPath = "files.json";
$filesData = file_exists($filesPath) ? json_decode(file_get_contents($filesPath), true) : [];

$usersPath = "users.json";
$users = file_exists($usersPath) ? json_decode(file_get_contents($usersPath), true) : [];

$siteSettings = file_exists('site_settings.json') ? json_decode(file_get_contents('site_settings.json'), true) : [
    "title"=>"Case File Archive",
    "about"=>"This archive contains semi-public investigation files. Visitors can view all cases, while admin can manage them securely.",
    "contact"=>"Email: detective@archive.com\nPhone: +62 812 3456 7890",
    "watermark_text"=>"CONFIDENTIAL"
];

$settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
$bgMusic = isset($settings['background_music']) ? $settings['background_music'] : 'default.mp3';

$infoPath = "info_content.json";
$infoContent = file_exists($infoPath) ? json_decode(file_get_contents($infoPath), true) : [];

$filesData = file_exists($filesPath) ? json_decode(file_get_contents($filesPath), true) : [];
$displayFiles = array_filter($filesData, function($f) {
    return $f['author'] === $_SESSION['username'];
});

if(isset($_POST['add'])){
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $downloadLink = trim($_POST['download_link']);
    $date = date("Y-m-d H:i:s");
    $thumbName = "";
    if(!empty($_FILES['thumbnail']['name'])){
        if(!is_dir('thumbs')) mkdir('thumbs',0777,true);
        $thumbName = time()."_".basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "thumbs/".$thumbName);
    }
    $fileURL = "";
    if(!empty($_FILES['upload_file']['name'])){
        $filePath = $_FILES['upload_file']['tmp_name'];
        $fileOriginalName = $_FILES['upload_file']['name'];
        $ch = curl_init();
        $postFields = [ 'reqtype'=>'fileupload', 'userhash'=>'6018bfdbe03db0d8cead98018', 'fileToUpload'=> new CURLFile($filePath, mime_content_type($filePath), $fileOriginalName) ];
        curl_setopt_array($ch,[ CURLOPT_URL=>'https://catbox.moe/user/api.php', CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true, CURLOPT_POSTFIELDS=>$postFields, CURLOPT_USERAGENT=>'Mozilla/5.0' ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if($response && filter_var($response, FILTER_VALIDATE_URL)){
            $fileURL = $response;
            $downloadLink = $response;
        } else {
            if(!is_dir('uploads')) mkdir('uploads',0777,true);
            $fileName = time()."_".$fileOriginalName;
            move_uploaded_file($filePath, "uploads/".$fileName);
            $fileURL = "uploads/".$fileName;
            $downloadLink = $fileURL;
        }
    }
    $filesData[] = [
        "title" => $title,
        "file" => $fileURL,
        "description" => $desc,
        "date" => $date,
        "thumbnail" => $thumbName,
        "download_link" => $downloadLink,
        "views" => 0,
        "viewers" => [],
        "author" => $_SESSION['username'],
        "role" => $_SESSION['role']
    ];
    file_put_contents($filesPath, json_encode($filesData, JSON_PRETTY_PRINT));
    header("Location: operator_dashboard.php?success=1");
    exit;
}

if(isset($_POST['edit_post'])){
    $index = intval($_POST['post_index']);
    if(isset($filesData[$index]) && $filesData[$index]['author'] === $_SESSION['username']){
        $filesData[$index]['title'] = $_POST['title'];
        $filesData[$index]['description'] = $_POST['description'];
        $filesData[$index]['download_link'] = $_POST['download_link'];
        if(!empty($_FILES['upload_file']['name'])){
            $filePath = $_FILES['upload_file']['tmp_name'];
            $fileOriginalName = $_FILES['upload_file']['name'];
            $ch = curl_init();
            $postFields = [ 'reqtype'=>'fileupload', 'userhash'=>'6018bfdbe03db0d8cead98018', 'fileToUpload'=> new CURLFile($filePath, mime_content_type($filePath), $fileOriginalName) ];
            curl_setopt_array($ch,[ CURLOPT_URL=>'https://catbox.moe/user/api.php', CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true, CURLOPT_POSTFIELDS=>$postFields, CURLOPT_USERAGENT=>'Mozilla/5.0' ]);
            $response = curl_exec($ch);
            curl_close($ch);
            if($response && filter_var($response, FILTER_VALIDATE_URL)){
                $filesData[$index]['file'] = $response;
                $filesData[$index]['download_link'] = $response;
            } else {
                if(!is_dir('uploads')) mkdir('uploads',0777,true);
                $fileName = time()."_".$fileOriginalName;
                move_uploaded_file($filePath, "uploads/".$fileName);
                $filesData[$index]['file'] = "uploads/".$fileName;
                $filesData[$index]['download_link'] = "uploads/".$fileName;
            }
        }
        if(!empty($_FILES['thumbnail']['name'])){
            if(!is_dir('thumbs')) mkdir('thumbs',0777,true);
            $thumbName = time()."_".basename($_FILES['thumbnail']['name']);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], "thumbs/".$thumbName);
            if(!empty($filesData[$index]['thumbnail'])) @unlink('thumbs/'.$filesData[$index]['thumbnail']);
            $filesData[$index]['thumbnail'] = $thumbName;
        }
        file_put_contents($filesPath, json_encode($filesData, JSON_PRETTY_PRINT));
        $edit_success = "Post updated successfully!";
    } else {
        $edit_error = "You can only edit your own posts.";
    }
}

if(isset($_GET['delete'])){
    $index = intval($_GET['delete']);
    if(isset($filesData[$index]) && $filesData[$index]['author'] === $_SESSION['username']){
        @unlink('uploads/'.$filesData[$index]['file']);
        @unlink('thumbs/'.$filesData[$index]['thumbnail']);
        array_splice($filesData, $index, 1);
        file_put_contents($filesPath, json_encode($filesData, JSON_PRETTY_PRINT));
        $delete_success = "Post deleted successfully!";
    } else {
        $delete_error = "You can only delete your own posts.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Operator Dashboard - <?=$siteSettings['title']?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<nav class="dashboard-navbar">
  <a href="index.php" class="dashboard-logo">
    <div class="dashboard-logo-icon">
      <i class="fas fa-folder"></i>
    </div>
    <div class="dashboard-logo-text">
      <span class="dashboard-logo-main"><?=$siteSettings['title']?></span>
      <span class="dashboard-logo-sub">Operator Dashboard</span>
    </div>
  </a>
  <div class="dashboard-user">
    <div class="dashboard-user-info">
      <div class="dashboard-user-name"><?=$_SESSION['username']?></div>
      <div class="dashboard-user-role"><?=$_SESSION['role']?></div>
    </div>
    <form method="POST" action="operator_dashboard.php" style="display:inline;">
        <button type="submit" name="logout" class="dashboard-logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </button>
    </form>
  </div>
</nav>

<div class="dashboard-container">
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Post added successfully!
        </div>
    <?php endif; ?>
    <?php if(isset($delete_success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $delete_success ?>
        </div>
    <?php endif; ?>
    <?php if(isset($edit_success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $edit_success ?>
        </div>
    <?php endif; ?>
    <?php if(isset($edit_error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $edit_error ?>
        </div>
    <?php endif; ?>
    <?php if(isset($delete_error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $delete_error ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-section">
        <h3 class="dashboard-section-title">
          <i class="fas fa-plus-circle"></i> Add New Case File
        </h3>
        <form method="post" enctype="multipart/form-data" class="dashboard-form">
          <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" placeholder="Case file title" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" placeholder="Case file description" class="form-control" rows="3" required></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">File Upload</label>
            <input type="file" name="upload_file" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">External Download Link (optional)</label>
            <input type="text" name="download_link" placeholder="https://..." class="form-control">
          </div>
          <div class="form-group">
            <button type="submit" name="add" class="btn btn-primary">
              <i class="fas fa-plus"></i> Add Post
            </button>
          </div>
        </form>
    </div>

    <div class="dashboard-section">
        <h3 class="dashboard-section-title">
            <i class="fas fa-tasks"></i> Manage My Posts (<?=count($displayFiles)?>)
        </h3>
        <div class="posts-grid">
            <?php foreach($displayFiles as $index=>$f): ?>
            <div class="post-card">
                <div class="post-thumbnail">
                    <?php if(!empty($f['thumbnail'])): ?>
                    <img src="thumbs/<?=$f['thumbnail']?>" alt="Thumbnail">
                    <?php else: ?>
                    <i class="fas fa-file-alt" style="font-size: 3rem; color: var(--text-muted);"></i>
                    <?php endif; ?>
                </div>
                <div class="post-content">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="post_index" value="<?=$index?>">
                        <div class="form-group">
                          <label class="form-label">Title</label>
                          <input type="text" name="title" value="<?=htmlspecialchars($f['title'])?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?=htmlspecialchars($f['description'])?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Download Link</label>
                            <input type="text" name="download_link" value="<?=htmlspecialchars($f['download_link'])?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Replace File</label>
                            <input type="file" name="upload_file" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Replace Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control">
                        </div>
                        <div class="post-actions">
                            <button type="submit" name="edit_post" class="btn btn-success btn-sm">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <a href="?delete=<?=$index?>" onclick="return confirm('Are you sure you want to delete this post?')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </form>
                    <div class="post-meta">
                        <div>
                            <i class="fas fa-user"></i> <?=$f['author']?>
                            <span class="role-badge role-<?=$f['role']?>"><?=$f['role']?></span>
                        </div>
                        <div>
                            <i class="fas fa-calendar"></i> <?=date('M j, Y', strtotime($f['date']))?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        button.classList.add('active');
        
        const tabId = button.getAttribute('data-tab');
        document.getElementById(tabId + '-tab').classList.add('active');
    });
});

document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        if(this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = input.closest('.form-group').querySelector('.file-preview');
                if(preview) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100px; max-height: 100px; margin-top: 10px;">`;
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
</body>
</html>
