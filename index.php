<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

include_once 'includes/functions.php';

$filesData = file_exists('files.json') ? json_decode(file_get_contents('files.json'), true) : [];
$updated = false;
foreach($filesData as $key => $file){
    if(!isset($file['author'])) { $filesData[$key]['author'] = 'Unknown'; $updated = true; }
    if(!isset($file['role'])) { $filesData[$key]['role'] = 'user'; $updated = true; }
    if(!isset($file['thumbnail'])) { $filesData[$key]['thumbnail'] = ''; $updated = true; }
    if(!isset($file['upload_file'])) { $filesData[$key]['upload_file'] = ''; $updated = true; }
    if(!isset($file['views'])) { $filesData[$key]['views'] = 0; $updated = true; }
    if(!isset($file['priority'])) { $filesData[$key]['priority'] = 'normal'; $updated = true; }
    if(!isset($file['title'])) { $filesData[$key]['title'] = $file['file'] ?? 'Untitled'; $updated = true; }
}
if($updated) file_put_contents('files.json', json_encode($filesData, JSON_PRETTY_PRINT));

$siteSettings = file_exists('site_settings.json') ? json_decode(file_get_contents('site_settings.json'), true) : [
    "title"=>"Case File Archive",
    "hero_title"=>"Case File Archive",
    "hero_subtitle"=>"A secure repository for investigation documents and case files.",
    "about"=>"This archive contains semi-public investigation files. Visitors can view all cases, while admin can manage them securely.",
    "contact"=>"Email: detective@archive.com\nPhone: +62 812 3456 7890",
    "watermark_text"=>"CONFIDENTIAL"
];
$settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
$bgMusic = isset($settings['background_music']) ? $settings['background_music'] : 'default.mp3';

foreach ($filesData as $key => $file) {
    if(!isset($_SESSION['viewed'][$key])){
        $filesData[$key]['views']++;
        $_SESSION['viewed'][$key] = true;
        $updated = true;
    }
}
if($updated) file_put_contents('files.json', json_encode($filesData,JSON_PRETTY_PRINT));

$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$filteredFiles = $filesData;
if($search !== ''){
    $filteredFiles = array_filter($filesData, function($f) use($search){
        return strpos(strtolower($f['title'] ?? $f['file']), $search)!==false || strpos(strtolower($f['description']), $search)!==false || strpos(strtolower($f['author']), $search)!==false;
    });
}
$filesWithKeys = [];
foreach($filteredFiles as $key => $file) {
    $filesWithKeys[$key] = $file;
}
krsort($filesWithKeys);
$filteredFiles = $filesWithKeys;
$perPage = 9;
$totalFiles = count($filteredFiles);
$totalPages = ceil($totalFiles / $perPage);
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$startIndex = ($page-1)*$perPage;
$filesDataPage = array_slice($filteredFiles, $startIndex, $perPage, true);
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = $isLoggedIn && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteSettings['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="fade-in">

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <div class="logo-icon">
                <i class="fas fa-user-secret"></i>
            </div>
            <div class="logo-text">
                <div class="logo-main"><?= htmlspecialchars($siteSettings['title']) ?></div>
                <div class="logo-sub">Anonymous Archive</div>
            </div>
        </a>
        <ul class="nav-menu">
            <li><a href="info.php" class="nav-link">Information</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="dashboard.php" class="nav-link admin">Control Panel</a></li>
                <li>
                    <form action="" method="post" style="display:inline;">
                        <button type="submit" name="logout" class="btn btn-outline">Logout</button>
                    </form>
                </li>
            <?php elseif ($isLoggedIn): ?>
                <li>
                    <form action="" method="post" style="display:inline;">
                        <button type="submit" name="logout" class="btn btn-outline">Logout</button>
                    </form>
                </li>
            <?php endif; ?>
        </ul>
        <button class="mobile-toggle" id="mobileToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="mobile-nav" id="mobileNav">
            <ul class="mobile-nav-list">
                <li><a href="info.php" class="nav-link">Information</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="dashboard.php" class="nav-link admin">Control Panel</a></li>
                    <li>
                        <form action="" method="post" style="display:inline;">
                            <button type="submit" name="logout" class="btn btn-outline">Logout</button>
                        </form>
                    </li>
                <?php elseif ($isLoggedIn): ?>
                    <li>
                        <form action="" method="post" style="display:inline;">
                            <button type="submit" name="logout" class="btn btn-outline">Logout</button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fas fa-shield-alt"></i> Secure Anonymous Repository
        </div>
        <h1 class="hero-title">
            Professional <span class="highlight">Document</span> Archive
        </h1>
        <p class="hero-subtitle">
            A sophisticated platform for secure document management with advanced privacy protection and anonymous access protocols
        </p>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?=count($filesData)?></span>
                <div class="stat-label">Documents</div>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?=array_sum(array_column($filesData, 'views'))?></span>
                <div class="stat-label">Total Access</div>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?=count(array_filter($filesData, function($f) { return $f['priority'] === 'critical'; }))?></span>
                <div class="stat-label">Critical Files</div>
            </div>
            <div class="stat-card">
                <span class="stat-value"><?=count(array_unique(array_column($filesData, 'author')))?></span>
                <div class="stat-label">Contributors</div>
            </div>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="search-container">
        <form class="search-form" method="GET" action="">
            <input type="text" name="search" class="search-input" placeholder="Search documents and archives..." value="<?=htmlspecialchars($search)?>" >
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</section>

<main class="main-content">
    <div class="content-header">
        <h2 class="content-title">
            <?php if($search): ?>
                Search Results
            <?php else: ?>
                Document Archive
            <?php endif; ?>
        </h2>
        <p class="content-subtitle">
            <?php if($search): ?>
                Found <?=count($filteredFiles)?> documents matching "<?=htmlspecialchars($search)?>"
            <?php else: ?>
                Browse our comprehensive collection of secure documents with role-based access control
            <?php endif; ?>
        </p>
    </div>
    <?php if(!empty($filesDataPage)): ?>
    <div class="files-grid">
        <?php foreach($filesDataPage as $key => $file): ?>
        <article class="file-card">
            <div class="card-image">
                <?php if(!empty($file['thumbnail'])): ?>
                <img src="thumbs/<?=htmlspecialchars($file['thumbnail'])?>" alt="<?=htmlspecialchars($file['title'] ?? $file['file'])?>">
                <?php else: ?>
                <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/a4d24ee1-c3bc-4d05-830a-de63b51d0bc7.png" alt="Professional anonymous document interface with encrypted file icons and secure access indicators">
                <?php endif; ?>
                <div class="image-overlay"></div>
                <div class="priority-indicator">
                    <?=formatPriority($file['priority'])?>
                </div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <a href="post.php?id=<?=$key?>"><?=htmlspecialchars($file['title'] ?? $file['file'])?></a>
                </h3>
                <p class="card-description">
                    <?=shortDesc($file['description'])?>
                </p>
                <div class="card-meta">
                    <div class="meta-author">
                        <i class="fas fa-user-circle"></i> <?=htmlspecialchars($file['author'])?> <?=formatRole($file['role'])?>
                    </div>
                    <div class="meta-stats">
                        <span><i class="fas fa-eye"></i> <?=formatViews($file['views'])?></span>
                        <span><i class="fas fa-clock"></i> <?=timeAgo($file['date'])?></span>
                    </div>
                </div>
                <div class="card-actions">
                    <?php if(!empty($file['download_link'])): ?>
                    <a href="<?=htmlspecialchars($file['download_link'])?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <?php endif; ?>
                    <a href="post.php?id=<?=$key?>" class="btn btn-outline">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php if($totalPages > 1): ?>
    <div class="pagination-container">
        <div class="pagination">
            <?php if($page > 1): ?>
            <a href="?page=<?=$page-1?>&search=<?=urlencode($search)?>" class="page-link">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            <?php $start = max(1, $page - 2); $end = min($totalPages, $page + 2); for($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?=$i?>&search=<?=urlencode($search)?>" class="page-link <?=$i==$page?'active':''?>">
                <?=$i?>
            </a>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
            <a href="?page=<?=$page+1?>&search=<?=urlencode($search)?>" class="page-link">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No Documents Found</h3>
        <p>
            <?php if($search): ?>
            No documents match your search criteria. Please try different keywords or check your search parameters.
            <?php else: ?>
            The archive database is currently empty. Please check back later for new document releases.
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
