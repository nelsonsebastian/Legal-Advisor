<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireAdmin();

// Validate input
$lawyer_id = $_POST['lawyer_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$lawyer_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE lawyers SET status = ? WHERE id = ?");
    $stmt->execute([$status, $lawyer_id]);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}