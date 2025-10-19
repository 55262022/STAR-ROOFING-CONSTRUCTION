<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['account_id']) && !empty($_SESSION['account_id']);
}

function isAdmin() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: ../public/404.php");
        exit();
    }
}

function getCurrentUserId() {
    return $_SESSION['account_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['role_id'] ?? null;
}
?>