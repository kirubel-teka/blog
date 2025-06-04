<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // Fetch entry to get file paths
        $stmt = $pdo->prepare("SELECT image_path, video_path FROM entries WHERE id = ?");
        $stmt->execute([$id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete files if they exist
        if ($entry['image_path'] && file_exists($entry['image_path'])) {
            unlink($entry['image_path']);
        }
        if ($entry['video_path'] && file_exists($entry['video_path'])) {
            unlink($entry['video_path']);
        }

        // Delete entry from database
        $stmt = $pdo->prepare("DELETE FROM entries WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Error deleting entry: " . $e->getMessage());
    }
}
header('Location: index.php');
exit;
?>