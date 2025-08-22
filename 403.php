<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-icon" style="color: var(--danger);">
            <i class="fas fa-lock"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Akses Ditolak</h1>
        <p class="error-message">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Ini adalah area terbatas.</p>
        <div class="error-actions">
            <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
            <button onclick="window.history.back()" class="btn btn-outline">Kembali</button>
        </div>
    </div>
</body>
</html>
