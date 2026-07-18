<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireAdmin(); // Or requireLawyer() depending on context

// Validate input
$appointment_id = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$appointment_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $appointment_id]);
    
    // If approved, create a case
    if ($status === 'approved') {
        // Generate case reference
        $case_reference = 'CASE-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Get appointment details
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();
        
        // Create case
        $stmt = $pdo->prepare("INSERT INTO cases (case_reference, appointment_id, client_id, lawyer_id, case_title, case_description, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'New')");
        $stmt->execute([
            $case_reference,
            $appointment_id,
            $appointment['client_id'],
            $appointment['lawyer_id'],
            $appointment['legal_issue_type'],
            $appointment['issue_description']
        ]);
    }
    
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}