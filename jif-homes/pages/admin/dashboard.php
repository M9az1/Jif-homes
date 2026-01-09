<?php
/**
 * JIF HOMES - Admin Dashboard
 */

$pageTitle = 'dashboard';
require_once __DIR__ . '/../../includes/admin_header.php';

// Get statistics
try {
    $db = getDB();
    
    // Total apartments
    $totalApartments = $db->query("SELECT COUNT(*) FROM apartments")->fetchColumn();
    $activeApartments = $db->query("SELECT COUNT(*) FROM apartments WHERE status = 'active'")->fetchColumn();
    
    // Total users
    $totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    
    // Total views
    $totalViews = $db->query("SELECT COALESCE(SUM(view_count), 0) FROM apartments")->fetchColumn();
    
    // Total feedback
    $totalFeedback = $db->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
    
    // Recent feedback
    $recentFeedback = $db->query("
        SELECT * FROM feedback 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    // Recent apartments
    $recentApartments = $db->query("
        SELECT a.*, 
               (SELECT image_path FROM apartment_images WHERE apartment_id = a.id AND is_primary = 1 LIMIT 1) as image
        FROM apartments a
        ORDER BY a.created_at DESC
        LIMIT 5
    ")->fetchAll();
    
} catch (PDOException $e) {
    $totalApartments = $activeApartments = $totalUsers = $totalViews = $totalFeedback = 0;
    $recentFeedback = $recentApartments = [];
}
?>

<!-- Stats Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon brown">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
            </div>
        </div>
        <div class="admin-stat-value"><?php echo number_format($totalApartments); ?></div>
        <div class="admin-stat-label"><?php echo __('total_apartments'); ?></div>
        <div class="admin-stat-change positive">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
            <?php echo $activeApartments; ?> <?php echo __('active'); ?>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
        </div>
        <div class="admin-stat-value"><?php echo number_format($totalUsers); ?></div>
        <div class="admin-stat-label"><?php echo __('total_users'); ?></div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon dark">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
        </div>
        <div class="admin-stat-value"><?php echo number_format($totalViews); ?></div>
        <div class="admin-stat-label"><?php echo $lang === 'ar' ? 'إجمالي المشاهدات' : 'Total Views'; ?></div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-header">
            <div class="admin-stat-icon muted">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"/></svg>
            </div>
        </div>
        <div class="admin-stat-value"><?php echo number_format($totalFeedback); ?></div>
        <div class="admin-stat-label"><?php echo $lang === 'ar' ? 'التقييمات' : 'Feedback'; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-xl); margin-top: var(--space-xl);">
    <!-- Recent Apartments -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'أحدث الشقق' : 'Recent Apartments'; ?></h3>
            <a href="<?php echo BASE_URL; ?>/pages/admin/apartments.php" class="btn btn-sm btn-outline"><?php echo __('view_all'); ?></a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (!empty($recentApartments)): ?>
                <?php foreach ($recentApartments as $apt): ?>
                <div style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-md) var(--space-lg); border-bottom: 1px solid var(--border-color);">
                    <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($apt['image'] ?? 'default.jpg'); ?>" 
                         style="width: 60px; height: 45px; object-fit: cover; border-radius: var(--radius-sm);"
                         onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=100&q=80'">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo sanitizeOutput($lang === 'ar' ? $apt['title_ar'] : $apt['title_en']); ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?php echo sanitizeOutput($lang === 'ar' ? $apt['city_ar'] : $apt['city_en']); ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600; color: var(--desert-brown);"><?php echo formatPrice($apt['price_per_day']); ?></div>
                        <span class="admin-badge admin-badge-<?php echo $apt['status'] === 'active' ? 'success' : 'warning'; ?>">
                            <?php echo __($apt['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: var(--space-2xl);">
                    <?php echo __('no_results'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Feedback -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'أحدث التقييمات' : 'Recent Feedback'; ?></h3>
            <a href="<?php echo BASE_URL; ?>/pages/admin/feedback.php" class="btn btn-sm btn-outline"><?php echo __('view_all'); ?></a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (!empty($recentFeedback)): ?>
                <?php foreach ($recentFeedback as $feedback): ?>
                <div style="padding: var(--space-md) var(--space-lg); border-bottom: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-xs);">
                        <div style="font-weight: 600;"><?php echo sanitizeOutput($feedback['name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            <?php echo formatDate($feedback['created_at']); ?>
                        </div>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo sanitizeOutput($feedback['message']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: var(--space-2xl);">
                    <?php echo __('no_results'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card" style="margin-top: var(--space-xl);">
    <div class="admin-card-header">
        <h3><?php echo $lang === 'ar' ? 'إجراءات سريعة' : 'Quick Actions'; ?></h3>
    </div>
    <div class="admin-card-body">
        <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/pages/admin/apartment-form.php" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <?php echo $lang === 'ar' ? 'إضافة شقة جديدة' : 'Add New Apartment'; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/admin/locations.php" class="btn btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?php echo __('manage_locations'); ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/admin/users.php" class="btn btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <?php echo __('manage_users'); ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/index.php" class="btn btn-outline" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15,3 21,3 21,9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <?php echo $lang === 'ar' ? 'عرض الموقع' : 'View Site'; ?>
            </a>
        </div>
    </div>
</div>

<style>
@media (max-width: 1024px) {
    .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
