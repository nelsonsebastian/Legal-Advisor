<?php
// ajax/get_lawyers_by_specialization.php
require_once '../config/database.php';
require_once '../includes/session.php';

if (!isset($_GET['specialization'])) {
    echo json_encode(['success' => false, 'message' => 'Specialization parameter required']);
    exit();
}

$specialization = $_GET['specialization'];

try {
    $stmt = $pdo->prepare("SELECT * FROM lawyers WHERE status = 'approved' AND JSON_CONTAINS(specializations, JSON_QUOTE(?)) ORDER BY average_rating DESC");
    $stmt->execute([$specialization]);
    $lawyers = $stmt->fetchAll();
    
    // Process specializations from JSON to array
    foreach ($lawyers as &$lawyer) {
        $lawyer['specializations'] = json_decode($lawyer['specializations'], true);
    }
    
    echo json_encode(['success' => true, 'lawyers' => $lawyers]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}