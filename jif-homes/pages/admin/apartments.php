<?php
/**
 * JIF HOMES - Admin Apartments Management
 */

$pageTitle = 'manage_apartments';
require_once __DIR__ . '/../../includes/admin_header.php';

// Handle actions
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Delete apartment
if ($action === 'delete' && $id > 0) {
    if (validateCSRFToken($_GET['token'] ?? '')) {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM apartments WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', $lang === 'ar' ? 'تم حذف الشقة بنجاح' : 'Apartment deleted successfully');
        } catch (PDOException $e) {
            setFlashMessage('error', $lang === 'ar' ? 'حدث خطأ' : 'An error occurred');
        }
    }
    header('Location: ' . BASE_URL . '/pages/admin/apartments.php');
    exit;
}

// Toggle status
if ($action === 'toggle' && $id > 0) {
    if (validateCSRFToken($_GET['token'] ?? '')) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE apartments SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', $lang === 'ar' ? 'تم تحديث الحالة' : 'Status updated');
        } catch (PDOException $e) {
            setFlashMessage('error', $lang === 'ar' ? 'حدث خطأ' : 'An error occurred');
        }
    }
    header('Location: ' . BASE_URL . '/pages/admin/apartments.php');
    exit;
}

// Get apartments
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

try {
    $db = getDB();
    
    $totalCount = $db->query("SELECT COUNT(*) FROM apartments")->fetchColumn();
    $pagination = paginate($totalCount, $perPage, $page);
    
    $stmt = $db->prepare("
        SELECT a.*, 
               (SELECT image_path FROM apartment_images WHERE apartment_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM apartments a 
        ORDER BY a.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $apartments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $apartments = [];
    $pagination = null;
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?php echo __('manage_apartments'); ?> (<?php echo $totalCount; ?>)</h3>
        <a href="<?php echo BASE_URL; ?>/pages/admin/apartment-form.php" class="btn btn-primary">
            + <?php echo __('add_apartment'); ?>
        </a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th><?php echo $lang === 'ar' ? 'الشقة' : 'Apartment'; ?></th>
                <th><?php echo __('location'); ?></th>
                <th><?php echo $lang === 'ar' ? 'السعر/يوم' : 'Price/Day'; ?></th>
                <th><?php echo __('bedrooms'); ?></th>
                <th><?php echo __('status'); ?></th>
                <th><?php echo __('actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($apartments)): ?>
                <?php foreach ($apartments as $apartment): ?>
                <tr>
                    <td>
                        <div class="apartment-cell">
                            <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($apartment['primary_image'] ?? 'default.jpg'); ?>" 
                                 class="apartment-thumb"
                                 onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&q=80'"
                                 alt="">
                            <div>
                                <div class="apartment-name">
                                    <?php echo sanitizeOutput($lang === 'ar' ? $apartment['title_ar'] : $apartment['title_en']); ?>
                                </div>
                                <div class="apartment-location">
                                    ID: <?php echo $apartment['id']; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo sanitizeOutput($lang === 'ar' ? $apartment['city_ar'] : $apartment['city_en']); ?></td>
                    <td><?php echo formatPrice($apartment['price_per_day']); ?></td>
                    <td><?php echo $apartment['bedrooms']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $apartment['status']; ?>">
                            <?php echo __($apartment['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="<?php echo BASE_URL; ?>/pages/apartment.php?id=<?php echo $apartment['id']; ?>" 
                               class="action-btn view" title="<?php echo __('view'); ?>" target="_blank"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/apartment-form.php?id=<?php echo $apartment['id']; ?>" 
                               class="action-btn edit" title="<?php echo __('edit'); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                            <a href="?action=toggle&id=<?php echo $apartment['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                               class="action-btn <?php echo $apartment['status'] === 'active' ? 'delete' : 'view'; ?>"
                               title="<?php echo $apartment['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                               <?php echo $apartment['status'] === 'active' ? '⏸️' : '▶️'; ?>
                            </a>
                            <a href="?action=delete&id=<?php echo $apartment['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                               class="action-btn delete" 
                               title="<?php echo __('delete'); ?>"
                               onclick="return confirmDelete();"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/></svg></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: var(--space-2xl); color: var(--medium-gray);">
                        <?php echo __('no_results'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($pagination && $pagination['total_pages'] > 1): ?>
    <div class="admin-pagination">
        <div class="pagination-info">
            <?php echo $lang === 'ar' ? 'عرض' : 'Showing'; ?> 
            <?php echo $pagination['offset'] + 1; ?>-<?php echo min($pagination['offset'] + $perPage, $totalCount); ?> 
            <?php echo $lang === 'ar' ? 'من' : 'of'; ?> <?php echo $totalCount; ?>
        </div>
        <div class="pagination-buttons">
            <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn"><?php echo $lang === 'ar' ? '→' : '←'; ?></a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($pagination['has_next']): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn"><?php echo $lang === 'ar' ? '←' : '→'; ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
