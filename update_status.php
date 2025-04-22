<?php
session_start();
require_once "db/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $planId = $_POST['plan_id'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE plans SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$planId, $userId]);
}

header("Location: dashboard.php");
exit();
