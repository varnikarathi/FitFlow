<?php
session_start();
require_once "db/db.php"; // Ensure this path is correct

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Delete user from DB using PDO
try {
    $sql = "DELETE FROM users WHERE id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        session_unset();
        session_destroy();
        header("Location: account_deleted.php");  // Redirect to the account deleted confirmation page
        exit();
    } else {
        echo "Error deleting account. Try again.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>