<?php
session_start();
include_once 'includes/functions.php';

$filesData = file_exists('files.json') ? json_decode(file_get_contents('files.json'), true) : [];
$siteSettings = file_exists('site_settings.json') ? json_decode(file_get_contents('site_settings.json'), true) : [
    "title"=>"Case File Archive",
    "about"=>"This archive contains semi-public investigation files. Visitors can view all cases, while admin can manage them securely.",
    "contact"=>"Email: detective@archive.com\nPhone: +62 812 3456 7890",
    "watermark_text"=>"CONFIDENTIAL"
];
$settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
$bgMusic = isset($settings['background_music']) ? $settings['background_music'] : 'default.mp3';

$postIndex = isset($_GET['id']) ? intval($_GET['id']) : -1;
$post = $filesData[$postIndex] ?? null;

if(!$post){
    header("Location: index.php");
    exit;
}

if(!isset($_SESSION['viewed_post'][$postIndex])){
    $filesData[$postIndex]['views'] = ($filesData[$postIndex]['views'] ?? 0) + 1;
    $_SESSION['viewed_post'][$postIndex] = true;
    file_put_contents('files.json', json_encode($filesData, JSON_PRETTY_PRINT));
    $post = $filesData[$postIndex];
}

$downloadTarget = !empty($post['download_link']) ? $post['download_link'] : '#';
$viewTarget = !empty($post['thumbnail']) ? 'thumbs/'.$post['thumbnail'] : 'https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/bb3c7074-7230-4c09-b850-08cbded10a96.png';

$relatedPosts = [];
foreach($filesData as $key => $file) {
    if($key !== $postIndex && (
        $file['author'] === $post['author'] || 
        $file['priority'] === $post['priority']
    )) {
        $relatedPosts[$key] = $file;
        if(count($relatedPosts) >= 3) break;
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="main-content">
  <div class="content-header">
    <nav class="breadcrumb">
      <a href="index.php">
        <i class="fas fa-home"></i>
        Archive
      </a>
      <span class="breadcrumb-separator">
        <i class="fas fa-chevron-right"></i>
      </span>
      <span>Case Details</span>
    </nav>
    <h1 class="content-title"><?=htmlspecialchars($post['title'] ?? $post['file'])?></h1>
  </div>

  <div class="file-card" style="display: flex; flex-direction: column;">
    <div class="card-image">
        <div class="priority-indicator priority-<?=$post['priority']?>">
            <?=ucfirst($post['priority'])?> Priority Case
        </div>
        <img src="<?=$viewTarget?>" alt="<?=htmlspecialchars($post['title'] ?? $post['file'])?>">
        <div class="image-overlay"></div>
    </div>
    <div class="card-content">
      <p class="card-description">
        <?=nl2br(htmlspecialchars($post['description']))?>
      </p>
      <div class="card-meta">
        <div class="meta-author">
            <i class="fas fa-user-circle"></i>
            <span><?=htmlspecialchars($post['author'] ?? 'Unknown')?></span>
            <?=formatRole($post['role'] ?? 'user')?>
        </div>
        <div class="meta-stats">
            <span><i class="fas fa-eye"></i> <?=formatViews(intval($post['views']))?></span>
            <span><i class="fas fa-clock"></i> <?=timeAgo($post['date'] ?? '')?></span>
        </div>
      </div>
      <div class="card-actions">
        <?php if($downloadTarget !== '#'): ?>
          <a href="<?=$downloadTarget?>" class="btn btn-primary" target="_blank">
            <i class="fas fa-download"></i>
            Download File
          </a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Archive
        </a>
      </div>
    </div>
  </div>

  <?php if(!empty($relatedPosts)): ?>
  <div class="related-posts">
    <h2 class="content-title" style="margin-top: 4rem;">
      <i class="fas fa-link"></i>
      Related Cases
    </h2>
    <div class="files-grid">
      <?php foreach($relatedPosts as $key => $related): ?>
        <article class="file-card">
          <div class="card-image">
            <?php if(!empty($related['thumbnail'])): ?>
              <img src="thumbs/<?=htmlspecialchars($related['thumbnail'])?>" alt="<?=htmlspecialchars($related['title'] ?? $related['file'])?>">
            <?php else: ?>
              <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/02d9b897-8e65-44c3-9ad3-4f73c27d3f11.png" alt="Related case file showing investigation document with evidence markers">
            <?php endif; ?>
          </div>
          <div class="card-content">
            <h3 class="card-title">
              <a href="post.php?id=<?=$key?>"><?=htmlspecialchars($related['title'] ?? $related['file'])?></a>
            </h3>
            <div class="card-meta">
              <div class="meta-author">
                <i class="fas fa-user-circle"></i> <?=htmlspecialchars($related['author'] ?? 'Unknown')?>
              </div>
              <div class="meta-stats">
                <span><i class="fas fa-eye"></i> <?=formatViews($related['views'] ?? 0)?></span>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>