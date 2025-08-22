<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $loginSuccess = false;
    $role = '';
    if ($username === "admin" && $password === "admin123") {
        $loginSuccess = true;
        $role = "admin";
    } elseif ($username === "operator" && $password === "op123") {
        $loginSuccess = true;
        $role = "operator";
    }
    if (!$loginSuccess) {
        $usersFile = "users.json";
        if (file_exists($usersFile)) {
            $users = json_decode(file_get_contents($usersFile), true);
            foreach ($users as $user) {
                if ($user['username'] === $username && $user['password'] === $password) {
                    $loginSuccess = true;
                    $role = $user['role'];
                    break;
                }
            }
        }
    }
    if ($loginSuccess) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        if ($role === "admin") {
            header("Location: dashboard.php");
        } else {
            header("Location: operator_dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
