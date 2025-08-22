<?php
session_start();

if(isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

if(!isset($_SESSION['role']) || $_SESSION['role']!=="admin"){
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
        $postFields = [
            'reqtype'=>'fileupload',
            'userhash'=>'6018bfdbe03db0d8cead98018',
            'fileToUpload'=> new CURLFile($filePath, mime_content_type($filePath), $fileOriginalName)
        ];
        curl_setopt_array($ch,[
            CURLOPT_URL=>'https://catbox.moe/user/api.php',
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POSTFIELDS=>$postFields,
            CURLOPT_USERAGENT=>'Mozilla/5.0'
        ]);
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
    header("Location: dashboard.php?success=1");
    exit;
}

if(isset($_POST['edit_post'])){
    $index = intval($_POST['post_index']);
    if(isset($filesData[$index])){
        $filesData[$index]['title'] = $_POST['title'];
        $filesData[$index]['description'] = $_POST['description'];

        if(!empty($_FILES['upload_file']['name'])){
            $filePath = $_FILES['upload_file']['tmp_name'];
            $fileOriginalName = $_FILES['upload_file']['name'];

            $ch = curl_init();
            $postFields = [
                'reqtype'=>'fileupload',
                'userhash'=>'6018bfdbe03db0d8cead98018',
            'fileToUpload'=> new CURLFile($filePath, mime_content_type($filePath), $fileOriginalName)
        ];
        curl_setopt_array($ch,[
            CURLOPT_URL=>'https://catbox.moe/user/api.php',
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POSTFIELDS=>$postFields,
            CURLOPT_USERAGENT=>'Mozilla/5.0'
        ]);
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
    } else {
        $filesData[$index]['download_link'] = $_POST['download_link'];
    }

    if(!empty($_FILES['thumbnail']['name'])){
        if(!is_dir('thumbs')) mkdir('thumbs',0777,true);
        $thumbName = time()."_".basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], "thumbs/".$thumbName);
        if(!empty($filesData[$index]['thumbnail'])) @unlink('thumbs/'.$filesData[$index]['thumbnail']);
        $filesData[$index]['thumbnail'] = $thumbName;
    }

    file_put_contents($filesPath,json_encode($filesData,JSON_PRETTY_PRINT));
    $edit_success = "Post updated!";
}
}

if(isset($_GET['delete'])){
    $index = intval($_GET['delete']);
    if(isset($filesData[$index])){
        if(!empty($filesData[$index]['file']) && file_exists($filesData[$index]['file'])) unlink($filesData[$index]['file']);
        if(!empty($filesData[$index]['thumbnail']) && file_exists('thumbs/'.$filesData[$index]['thumbnail'])) unlink('thumbs/'.$filesData[$index]['thumbnail']);
        array_splice($filesData, $index,1);
        file_put_contents($filesPath,json_encode($filesData,JSON_PRETTY_PRINT));
        $delete_success = "Post deleted!";
    }
}

if(isset($_POST['add_user'])){
    $newUser = trim($_POST['new_username']);
    $newPass = trim($_POST['new_password']);
    $newRole = $_POST['new_role'];
    $users[] = ["username"=>$newUser,"password"=>$newPass,"role"=>$newRole];
    file_put_contents($usersPath,json_encode($users,JSON_PRETTY_PRINT));
    $user_msg = "User added!";
}

if(isset($_POST['edit_user'])){
    $i = intval($_POST['user_index']);
    if(isset($users[$i])){
        $users[$i]['username'] = $_POST['username'];
        $users[$i]['password'] = $_POST['password'];
        $users[$i]['role'] = $_POST['role'];
        file_put_contents($usersPath,json_encode($users,JSON_PRETTY_PRINT));
        $user_msg = "User updated!";
    }
}

if(isset($_GET['delete_user'])){
    $delIndex = intval($_GET['delete_user']);
    if(isset($users[$delIndex]) && $users[$delIndex]['username']!==$_SESSION['username']){
        array_splice($users,$delIndex,1);
        file_put_contents($usersPath,json_encode($users,JSON_PRETTY_PRINT));
        $user_msg = "User deleted!";
    }
}

if(isset($_POST['save_settings'])){
    $siteSettings['title'] = $_POST['site_title'];
    $siteSettings['about'] = $_POST['site_about'];
    $siteSettings['contact'] = $_POST['site_contact'];
    $siteSettings['watermark_text'] = $_POST['watermark_text'];
    file_put_contents('site_settings.json',json_encode($siteSettings,JSON_PRETTY_PRINT));

    if(!empty($_FILES['bg_music_file']['name'])){
        if(!is_dir('music')) mkdir('music',0777,true);
        $musicName = basename($_FILES['bg_music_file']['name']);
        move_uploaded_file($_FILES['bg_music_file']['tmp_name'], "music/".$musicName);
        $settings['background_music'] = $musicName;
    } else {
        $settings['background_music'] = $_POST['bg_music'];
    }
    file_put_contents('settings.json',json_encode($settings,JSON_PRETTY_PRINT));
    $settings_success = "Settings saved!";
}

if(isset($_POST['save_info'])){
    $infoContent['title'] = $_POST['title'];
    $infoContent['intro'] = $_POST['intro'];
    $infoContent['purpose'] = $_POST['purpose'];
    $infoContent['navigation'] = $_POST['navigation'];
    $infoContent['theme'] = $_POST['theme'];
    file_put_contents($infoPath, json_encode($infoContent, JSON_PRETTY_PRINT));
    $info_success = "Info page updated!";
}

$displayFiles = array_reverse($filesData,true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - <?=$siteSettings['title']?></title>
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
      <span class="dashboard-logo-sub">Admin Dashboard</span>
    </div>
  </a>
  
  <div class="dashboard-user">
    <div class="dashboard-user-info">
      <div class="dashboard-user-name"><?=$_SESSION['username']?></div>
      <div class="dashboard-user-role"><?=$_SESSION['role']?></div>
    </div>
    <form method="POST" action="dashboard.php" style="display:inline;">
        <button type="submit" name="logout" class="dashboard-logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </button>
    </form>
  </div>
</nav>

<div class="dashboard-container">
  <?php if(isset($delete_success)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?=$delete_success?>
    </div>
  <?php endif; ?>
  
  <?php if(isset($edit_success)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?=$edit_success?>
    </div>
  <?php endif; ?>
  
  <?php if(isset($settings_success)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?=$settings_success?>
    </div>
  <?php endif; ?>
  
  <?php if(isset($user_msg)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?=$user_msg?>
    </div>
  <?php endif; ?>
  
  <?php if(isset($info_success)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?=$info_success?>
    </div>
  <?php endif; ?>

  <div class="tab-container">
    <div class="tab-buttons">
      <button class="tab-button active" data-tab="posts">
        <i class="fas fa-file"></i> Posts
      </button>
      <button class="tab-button" data-tab="settings">
        <i class="fas fa-cog"></i> Settings
      </button>
      <button class="tab-button" data-tab="users">
        <i class="fas fa-users"></i> Users
      </button>
      <button class="tab-button" data-tab="info">
        <i class="fas fa-info-circle"></i> Info Page
      </button>
    </div>

    <div class="tab-content active" id="posts-tab">
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
          <i class="fas fa-tasks"></i> Manage Posts (<?=count($displayFiles)?>)
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

    <div class="tab-content" id="settings-tab">
      <div class="dashboard-section">
        <h3 class="dashboard-section-title">
          <i class="fas fa-cog"></i> Site Settings
        </h3>
        
        <form method="post" enctype="multipart/form-data" class="dashboard-form">
          <div class="form-group">
            <label class="form-label">Site Title</label>
            <input type="text" name="site_title" class="form-control" value="<?=htmlspecialchars($siteSettings['title'])?>">
          </div>
          
                    <div class="form-group">
            <label class="form-label">About Text</label>
            <textarea name="site_about" class="form-control" rows="3"><?=htmlspecialchars($siteSettings['about'])?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Contact Information</label>
            <textarea name="site_contact" class="form-control" rows="3"><?=htmlspecialchars($siteSettings['contact'])?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Watermark Text</label>
            <input type="text" name="watermark_text" class="form-control" value="<?=htmlspecialchars($siteSettings['watermark_text'])?>">
          </div>
          
          <div class="form-group">
            <label class="form-label">Background Music</label>
            <select name="bg_music" class="form-control">
              <option value="default.mp3" <?=$bgMusic=='default.mp3'?'selected':''?>>Default Music</option>
              <option value="none" <?=$bgMusic=='none'?'selected':''?>>No Music</option>
              <?php
              if(is_dir('music')) {
                  $musicFiles = scandir('music');
                  foreach($musicFiles as $file) {
                      if($file != '.' && $file != '..' && preg_match('/\.(mp3|wav|ogg)$/i', $file)) {
                          echo '<option value="'.$file.'" '.($bgMusic==$file?'selected':'').'>'.$file.'</option>';
                      }
                  }
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Upload New Music File</label>
            <input type="file" name="bg_music_file" accept="audio/*" class="form-control">
          </div>
          
          <div class="form-group">
            <button type="submit" name="save_settings" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Settings
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="tab-content" id="users-tab">
      <div class="dashboard-section">
        <h3 class="dashboard-section-title">
          <i class="fas fa-user-plus"></i> Add New User
        </h3>
        
        <form method="post" class="dashboard-form">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="new_username" class="form-control" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Role</label>
            <select name="new_role" class="form-control">
              <option value="operator">Operator</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          
          <div class="form-group">
            <button type="submit" name="add_user" class="btn btn-primary">
              <i class="fas fa-plus"></i> Add User
            </button>
          </div>
        </form>
      </div>

      <div class="dashboard-section">
        <h3 class="dashboard-section-title">
          <i class="fas fa-users-cog"></i> Manage Users (<?=count($users)?>)
        </h3>
        
        <table class="users-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Password</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($users as $index=>$u): ?>
            <tr>
              <form method="post">
                <input type="hidden" name="user_index" value="<?=$index?>">
                <td>
                  <input type="text" name="username" value="<?=htmlspecialchars($u['username'])?>" class="form-control">
                </td>
                <td>
                  <input type="password" name="password" value="<?=htmlspecialchars($u['password'])?>" class="form-control">
                </td>
                <td>
                  <select name="role" class="form-control">
                    <option value="operator" <?=$u['role']=='operator'?'selected':''?>>Operator</option>
                    <option value="admin" <?=$u['role']=='admin'?'selected':''?>>Admin</option>
                  </select>
                </td>
                <td>
                  <button type="submit" name="edit_user" class="btn btn-success btn-sm">
                    <i class="fas fa-save"></i>
                  </button>
                  <?php if($u['username'] !== $_SESSION['username']): ?>
                  <a href="?delete_user=<?=$index?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </form>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-content" id="info-tab">
      <div class="dashboard-section">
        <h3 class="dashboard-section-title">
          <i class="fas fa-info-circle"></i> Edit Info Page Content
        </h3>
        
        <form method="post" class="dashboard-form">
          <div class="form-group">
            <label class="form-label">Page Title</label>
            <input type="text" name="title" class="form-control" value="<?=isset($infoContent['title'])?htmlspecialchars($infoContent['title']):''?>">
          </div>
          
          <div class="form-group">
            <label class="form-label">Introduction</label>
            <textarea name="intro" class="form-control" rows="3"><?=isset($infoContent['intro'])?htmlspecialchars($infoContent['intro']):''?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Purpose</label>
            <textarea name="purpose" class="form-control" rows="3"><?=isset($infoContent['purpose'])?htmlspecialchars($infoContent['purpose']):''?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Navigation Guide</label>
            <textarea name="navigation" class="form-control" rows="3"><?=isset($infoContent['navigation'])?htmlspecialchars($infoContent['navigation']):''?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Theme Information</label>
            <textarea name="theme" class="form-control" rows="3"><?=isset($infoContent['theme'])?htmlspecialchars($infoContent['theme']):''?></textarea>
          </div>
          
          <div class="form-group">
            <button type="submit" name="save_info" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Info Page
            </button>
          </div>
        </form>
      </div>
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
