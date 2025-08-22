<?php
http_response_code(404);
include 'includes/header.php';
?>
<div class="error-page">
    <div class="error-container">
        <div class="error-icon" style="color: var(--warning);">
            <i class="fas fa-unlink"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Halaman Tidak Ditemukan</h1>
        <p class="error-message">Maaf, halaman yang Anda cari tidak ada. Mungkin halaman tersebut sudah dihapus atau dipindahkan.</p>
        <div class="error-actions">
            <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
            <button onclick="window.history.back()" class="btn btn-outline">Kembali</button>
        </div>
    </div>
</div>
</body>
</html>
