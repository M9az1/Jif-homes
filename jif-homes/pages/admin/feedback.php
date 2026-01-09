<?php
/**
 * JIF HOMES - Admin Feedback Management
 */

$pageTitle = 'manage_feedback';
require_once __DIR__ . '/../../includes/admin_header.php';

// Handle actions
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id > 0 && validateCSRFToken($_GET['token'] ?? '')) {
    try {
        $db = getDB();
        $db->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
        setFlashMessage('success', $lang === 'ar' ? 'تم الحذف' : 'Deleted');
    } catch (PDOException $e) {}
    header('Location: ' . BASE_URL . '/pages/admin/feedback.php');
    exit;
}

if ($action === 'mark_read' && $id > 0 && validateCSRFToken($_GET['token'] ?? '')) {
    try {
        $db = getDB();
        $db->prepare("UPDATE feedback SET status = 'read' WHERE id = ?")->execute([$id]);
    } catch (PDOException $e) {}
    header('Location: ' . BASE_URL . '/pages/admin/feedback.php');
    exit;
}

// Get feedback
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

try {
    $db = getDB();
    $totalCount = $db->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
    $pagination = paginate($totalCount, $perPage, $page);
    
    $stmt = $db->prepare("SELECT * FROM feedback ORDER BY status ASC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    $feedbacks = [];
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?php echo __('manage_feedback'); ?> (<?php echo $totalCount; ?>)</h3>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th><?php echo $lang === 'ar' ? 'المرسل' : 'Sender'; ?></th>
                <th><?php echo __('feedback_type'); ?></th>
                <th><?php echo __('message'); ?></th>
                <th><?php echo $lang === 'ar' ? 'التاريخ' : 'Date'; ?></th>
                <th><?php echo __('status'); ?></th>
                <th><?php echo __('actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr style="<?php echo $feedback['status'] === 'new' ? 'background: var(--warning-light);' : ''; ?>">
                    <td>
                        <strong><?php echo sanitizeOutput($feedback['name']); ?></strong>
                        <br><small><?php echo sanitizeOutput($feedback['email']); ?></small>
                        <?php if ($feedback['phone']): ?>
                        <br><small dir="ltr"><?php echo sanitizeOutput($feedback['phone']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $types = [
                            'general' => $lang === 'ar' ? 'عام' : 'General',
                            'complaint' => $lang === 'ar' ? 'شكوى' : 'Complaint',
                            'suggestion' => $lang === 'ar' ? 'اقتراح' : 'Suggestion',
                            'inquiry' => $lang === 'ar' ? 'استفسار' : 'Inquiry'
                        ];
                        echo $types[$feedback['feedback_type']] ?? $feedback['feedback_type'];
                        ?>
                    </td>
                    <td style="max-width: 300px;">
                        <div style="max-height: 60px; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo sanitizeOutput(substr($feedback['message'], 0, 150)); ?>
                            <?php if (strlen($feedback['message']) > 150) echo '...'; ?>
                        </div>
                    </td>
                    <td><?php echo formatDate($feedback['created_at']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $feedback['status'] === 'new' ? 'pending' : 'active'; ?>">
                            <?php echo $feedback['status'] === 'new' ? ($lang === 'ar' ? 'جديد' : 'New') : ($lang === 'ar' ? 'مقروء' : 'Read'); ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <?php if ($feedback['status'] === 'new'): ?>
                            <a href="?action=mark_read&id=<?php echo $feedback['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                               class="action-btn view" title="Mark as read">
                               <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                            </a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?php echo $feedback['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                               class="action-btn delete" onclick="return confirmDelete();">
                               <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/></svg>
                            </a>
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
            <?php echo $lang === 'ar' ? 'صفحة' : 'Page'; ?> <?php echo $pagination['current_page']; ?> 
            <?php echo $lang === 'ar' ? 'من' : 'of'; ?> <?php echo $pagination['total_pages']; ?>
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
