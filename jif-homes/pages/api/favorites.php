<?php
/**
 * JIF HOMES - Favorites API
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/translations.php';

$lang = getCurrentLanguage();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'login_required']);
    exit;
}

$action = $_POST['action'] ?? '';
$apartmentId = (int)($_POST['apartment_id'] ?? 0);

if (!$apartmentId) {
    echo json_encode(['success' => false, 'error' => 'Invalid apartment']);
    exit;
}

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    if ($action === 'toggle') {
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND apartment_id = ?");
        $stmt->execute([$userId, $apartmentId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Remove
            $db->prepare("DELETE FROM favorites WHERE user_id = ? AND apartment_id = ?")->execute([$userId, $apartmentId]);
            echo json_encode([
                'success' => true, 
                'action' => 'removed',
                'message' => $lang === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from favorites'
            ]);
        } else {
            // Add
            $db->prepare("INSERT INTO favorites (user_id, apartment_id, created_at) VALUES (?, ?, NOW())")->execute([$userId, $apartmentId]);
            echo json_encode([
                'success' => true, 
                'action' => 'added',
                'message' => $lang === 'ar' ? 'تمت الإضافة إلى المفضلة' : 'Added to favorites'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
