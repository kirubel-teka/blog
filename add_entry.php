<?php
require_once 'config.php';

// Initialize variables
$content = trim($_POST['content'] ?? '');
$image_path = null;
$video_path = null;
$upload_dir = 'uploads/';
$allowed_image_types = ['image/jpeg', 'image/png'];
$allowed_video_types = ['video/mp4', 'video/webm'];
$max_file_size = 19 * 1024 * 1024; // 19MB
$errors = [];

// Check if uploads directory exists
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die("Error: Could not create uploads directory.");
    }
}

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['image'];
    if (!in_array($image['type'], $allowed_image_types)) {
        $errors[] = "Invalid image type: " . $image['type'] . ". Allowed: JPEG, PNG.";
    } elseif ($image['size'] > $max_file_size) {
        $errors[] = "Image file too large: " . $image['size'] . " bytes. Max: 8MB.";
    } else {
        $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $image_path = $upload_dir . uniqid('img_') . '.' . $image_ext;
        if (!move_uploaded_file($image['tmp_name'], $image_path)) {
            $errors[] = "Failed to move uploaded image: " . $image['name'];
            error_log("Failed to move image: " . $image['name']);
        }
    }
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $errors[] = "Image upload error: " . $_FILES['image']['error'];
}

// Handle video upload
if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
    $video = $_FILES['video'];
    if (!in_array($video['type'], $allowed_video_types)) {
        $errors[] = "Invalid video type: " . $video['type'] . ". Allowed: MP4, WEBM.";
    } elseif ($video['size'] > $max_file_size) {
        $errors[] = "Video file too large: " . $video['size'] . " bytes. Max: 8MB.";
    } else {
        $video_ext = pathinfo($video['name'], PATHINFO_EXTENSION);
        $video_path = $upload_dir . uniqid('vid_') . '.' . $video_ext;
        if (!move_uploaded_file($video['tmp_name'], $video_path)) {
            $errors[] = "Failed to move uploaded video: " . $video['name'];
            error_log("Failed to move video: " . $video['name']);
        }
    }
} elseif (isset($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
    $errors[] = "Video upload error: " . $_FILES['video']['error'];
}

// Store errors in session for display
if (!empty($errors)) {
    session_start();
    $_SESSION['upload_errors'] = $errors;
}

// Insert entry into database if content or media is provided
if (!empty($content) || $image_path || $video_path) {
    try {
        $stmt = $pdo->prepare("INSERT INTO entries (content, image_path, video_path) VALUES (?, ?, ?)");
        $stmt->execute([$content, $image_path, $video_path]);
    } catch (PDOException $e) {
        die("Error adding entry: " . $e->getMessage());
    }
} elseif (empty($errors)) {
    session_start();
    $_SESSION['upload_errors'] = ["No content or media provided for entry."];
}

header('Location: index.php');
exit;
?>