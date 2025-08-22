<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'includes/functions.php';

$siteSettings = file_exists('site_settings.json') ? json_decode(file_get_contents('site_settings.json'), true) : [
    "title"=>"Case File Archive",
    "hero_title"=>"Case File Archive",
    "hero_subtitle"=>"A secure repository for investigation documents and case files.",
    "about"=>"This archive contains semi-public investigation files. Visitors can view all cases, while admin can manage them securely.",
    "contact"=>"Email: detective@archive.com\nPhone: +62 812 3456 7890",
    "watermark_text"=>"CONFIDENTIAL"
];
$infoContent = file_exists('info_content.json') ? json_decode(file_get_contents('info_content.json'), true) : [
    "title" => "Information Portal",
    "intro" => "Welcome to the information portal of the Case File Archive system.",
    "purpose" => "This system serves as a secure repository for investigation documents and case files.",
    "navigation" => "Use the navigation menu to browse files or access the admin dashboard if you have privileges.",
    "theme" => "Designed with a professional investigative aesthetic for easy document management."
];
$settings = file_exists('settings.json') ? json_decode(file_get_contents('settings.json'), true) : [];
$bgMusic = isset($settings['background_music']) ? $settings['background_music'] : 'default.mp3';

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($infoContent['title'] ?? 'Info') ?> - <?= htmlspecialchars($siteSettings['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .info-container {
            max-width: 900px;
            margin: 2rem auto;
            background: var(--bg-card);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 3rem;
            animation: fadeIn 0.8s ease-out;
        }
        .info-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }
        .info-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .info-intro {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        .info-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-muted);
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title i {
            color: var(--primary);
        }
        .section-content {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.7;
            padding-left: 2rem;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            text-decoration: none;
            cursor: pointer;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-secondary);
        }
        .btn-back:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            border-color: var(--border-primary);
        }
        .footer {
            margin-top: 2rem;
        }
    </style>
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
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="main-content">
        <div class="info-container">
            <div class="info-header">
                <h1 class="info-title"><?= htmlspecialchars($infoContent['title'] ?? 'Information Portal') ?></h1>
                <p class="info-intro"><?= nl2br(htmlspecialchars($infoContent['intro'] ?? 'Welcome to the information portal of the Case File Archive system.')) ?></p>
            </div>
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fas fa-bullseye"></i> Purpose
                </h3>
                <div class="section-content">
                    <?= nl2br(htmlspecialchars($infoContent['purpose'] ?? 'This system serves as a secure repository for investigation documents and case files. Authorized personnel can manage and access confidential records while maintaining proper security protocols.')) ?>
                </div>
            </div>
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fas fa-compass"></i> Navigation
                </h3>
                <div class="section-content">
                    <?= nl2br(htmlspecialchars($infoContent['navigation'] ?? 'Use the main navigation to browse case files. The search functionality allows you to find specific documents by title, description, or author. Admin users can access the dashboard for content management.')) ?>
                </div>
            </div>
            <div class="info-section">
                <h3 class="section-title">
                    <i class="fas fa-paint-brush"></i> Theme & Design
                </h3>
                <div class="section-content">
                    <?= nl2br(htmlspecialchars($infoContent['theme'] ?? 'The interface features a professional investigative aesthetic with a clean, modern design. Color-coded priority indicators and role badges help quickly identify content importance and authorship.')) ?>
                </div>
            </div>
            <div style="text-align: center; margin-top: 3rem;">
                <a href="index.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About the Archive</h3>
                <p><?= nl2br(htmlspecialchars($siteSettings['about'])) ?></p>
            </div>
            <div class="footer-section">
                <h3>Secure Contact</h3>
                <p><?= nl2br(htmlspecialchars($siteSettings['contact'])) ?></p>
            </div>
            <div class="footer-section">
                <h3>Quick Navigation</h3>
                <ul class="footer-links">
                    <li><a href="info.php"><i class="fas fa-info-circle"></i> System Information</a></li>
                    <?php if (isset($_SESSION['username'])): ?>
                        <li><a href="dashboard.php"><i class="fas fa-cog"></i> Administrative Panel</a></li>
                    <?php endif; ?>
                    <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    <li><a href="#"><i class="fas fa-file-contract"></i> Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> <?= htmlspecialchars($siteSettings['title']) ?> | Professional Anonymous Archive System</p>
        </div>
    </footer>
    <?php if (!empty($bgMusic) && $bgMusic !== 'none'): ?>
        <div class="music-player">
            <button class="music-control" id="toggleMusic" aria-label="Toggle music">
                <i class="fas fa-play" id="musicIcon"></i>
            </button>
            <div class="music-info">ambient</div>
            <div class="music-progress">
                <div class="music-progress-bar" id="musicProgress"></div>
            </div>
        </div>
        <audio id="bgMusic" loop hidden>
            <source src="music/<?= htmlspecialchars($bgMusic) ?>" type="audio/mpeg">
        </audio>
    <?php endif; ?>
    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileNav = document.getElementById('mobileNav');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                mobileToggle.classList.toggle('active');
                mobileNav.classList.toggle('show');
            });
            document.addEventListener('click', (e) => {
                if (!mobileToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                    mobileToggle.classList.remove('active');
                    mobileNav.classList.remove('show');
                }
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    mobileToggle.classList.remove('active');
                    mobileNav.classList.remove('show');
                }
            });
        }

        const audio = document.getElementById('bgMusic');
        if (audio) {
            const toggleBtn = document.getElementById('toggleMusic');
            const musicIcon = document.getElementById('musicIcon');
            const progressBar = document.getElementById('musicProgress');
            let isPlaying = localStorage.getItem('musicPlaying') === 'true';
            let lastTime = parseFloat(localStorage.getItem('bgTime')) || 0;
            audio.currentTime = lastTime;
            if (isPlaying) {
                audio.play().catch(e => console.log('Autoplay prevented by browser policy'));
                musicIcon.className = 'fas fa-pause';
            }
            toggleBtn.addEventListener('click', () => {
                if (audio.paused) {
                    audio.play().then(() => {
                        musicIcon.className = 'fas fa-pause';
                        localStorage.setItem('musicPlaying', 'true');
                    }).catch(e => {
                        console.log('Audio playback failed:', e);
                    });
                } else {
                    audio.pause();
                    musicIcon.className = 'fas fa-play';
                    localStorage.setItem('musicPlaying', 'false');
                }
            });
            audio.addEventListener('timeupdate', () => {
                if (audio.duration) {
                    const progress = (audio.currentTime / audio.duration) * 100;
                    progressBar.style.width = progress + '%';
                    localStorage.setItem('bgTime', audio.currentTime.toString());
                }
            });
            audio.addEventListener('error', (e) => {
                console.log('Audio error:', e);
                musicIcon.className = 'fas fa-exclamation-triangle';
            });
        }
    </script>
</body>
</html>
