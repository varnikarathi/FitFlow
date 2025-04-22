<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Redirect if not logged in (can be handled here instead of top page)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>
