<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2 class="login-title">Login</h2>
        <?php if(isset($error)): ?>
        <div class="alert-error"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" class="form-control" required>
            <input type="password" name="password" placeholder="Password" class="form-control" required>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>
