<?php
/**
 * JIF HOMES - Admin Users Management
 */

$pageTitle = 'manage_users';
require_once __DIR__ . '/../../includes/admin_header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

try {
    $db = getDB();
    $totalCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $pagination = paginate($totalCount, $perPage, $page);
    
    $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $pagination['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><?php echo __('manage_users'); ?> (<?php echo $totalCount ?? 0; ?>)</h3>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo $lang === 'ar' ? 'الاسم' : 'Name'; ?></th>
                <th><?php echo __('email'); ?></th>
                <th><?php echo __('phone'); ?></th>
                <th><?php echo $lang === 'ar' ? 'الدور' : 'Role'; ?></th>
                <th><?php echo $lang === 'ar' ? 'تاريخ التسجيل' : 'Registered'; ?></th>
                <th><?php echo __('status'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['id']; ?></td>
                    <td><?php echo sanitizeOutput($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo sanitizeOutput($user['email']); ?></td>
                    <td dir="ltr"><?php echo sanitizeOutput($user['phone'] ?? '-'); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['role'] === 'admin' ? 'pending' : 'active'; ?>">
                            <?php echo $user['role'] === 'admin' ? ($lang === 'ar' ? 'مدير' : 'Admin') : ($lang === 'ar' ? 'مستخدم' : 'User'); ?>
                        </span>
                    </td>
                    <td><?php echo formatDate($user['created_at']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? __('active') : __('inactive'); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: var(--space-2xl); color: var(--medium-gray);">
                        <?php echo __('no_results'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
