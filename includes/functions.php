<?php
session_start();

require_once __DIR__ . '/../config.php';

// Database connection
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            error_log("Database connection error: " . $db->connect_error);
            die("Error connecting to database. Please try again later.");
        }
        $db->set_charset("utf8mb4");
    }
    return $db;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /UAS_PHP/login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /UAS_PHP/index.php');
        exit();
    }
}

// Sanitize input - Prevents XSS attacks
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Get user by email
function getUserByEmail($email) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get user by ID
function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Logout function
function logout() {
    session_destroy();
    header('Location: /UAS_PHP/index.php');
    exit();
}

// Upload image function
function uploadImage($file) {
    $target_dir = __DIR__ . "/../assets/images/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return '';
    }

    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return '';
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return '';
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return 'assets/images/' . $new_filename;
    } else {
        return '';
    }
}
?>
