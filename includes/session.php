<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Check user type
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Get user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Check if user is admin
function isAdmin() {
    return getUserType() === 'admin';
}

// Check if user is lawyer
function isLawyer() {
    return getUserType() === 'lawyer';
}

// Check if user is client
function isClient() {
    return getUserType() === 'client';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}


// session.php (add these functions)
function requireClient() {
    requireLogin();
    if (!isClient()) {
        header('Location: login.php');
        exit();
    }
}

function requireLawyer() {
    requireLogin();
    if (!isLawyer()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: login.php');
        exit();
    }
}

// Generate case reference number
function generateCaseReference() {
    return 'CASE-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Format date for display
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>