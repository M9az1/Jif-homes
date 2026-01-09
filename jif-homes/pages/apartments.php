<?php
/**
 * JIF HOMES - Apartments Listing Page
 */

$pageTitle = 'apartments';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$location = sanitizeInput($_GET['location'] ?? '');
$checkIn = sanitizeInput($_GET['check_in'] ?? '');
$checkOut = sanitizeInput($_GET['check_out'] ?? '');
$guests = (int)($_GET['guests'] ?? 0);
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 0);
$bedrooms = (int)($_GET['bedrooms'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;

// Build query
$where = ["status = 'active'"];
$params = [];

if ($location) {
    $where[] = "city_en = ?";
    $params[] = $location;
}

if ($guests > 0) {
    $where[] = "max_guests >= ?";
    $params[] = $guests;
}

if ($minPrice > 0) {
    $where[] = "price_per_day >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "price_per_day <= ?";
    $params[] = $maxPrice;
}

if ($bedrooms > 0) {
    $where[] = "bedrooms >= ?";
    $params[] = $bedrooms;
}

$whereClause = implode(' AND ', $where);

try {
    $db = getDB();
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM apartments WHERE $whereClause");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    
    // Get apartments
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("
        SELECT a.*, 
               (SELECT image_path FROM apartment_images WHERE apartment_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM apartments a 
        WHERE $whereClause 
        ORDER BY a.featured DESC, a.created_at DESC 
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $apartments = $stmt->fetchAll();
    
    $pagination = paginate($totalCount, $perPage, $page);
    
} catch (PDOException $e) {
    $apartments = [];
    $pagination = null;
}
?>

<section class="section" style="padding-top: var(--space-xl);">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('apartments'); ?></h2>
            <div class="section-divider"></div>
        </div>
        
        <!-- Filters -->
        <div class="search-section" style="margin-bottom: var(--space-2xl);">
            <form action="" method="GET" class="search-form" id="filterForm">
                <div class="search-group">
                    <label for="location"><?php echo __('location'); ?></label>
                    <select name="location" id="location" class="form-select">
                        <option value=""><?php echo __('select_city'); ?></option>
                        <option value="riyadh" <?php echo $location === 'riyadh' ? 'selected' : ''; ?>><?php echo __('riyadh'); ?></option>
                        <option value="jeddah" <?php echo $location === 'jeddah' ? 'selected' : ''; ?>><?php echo __('jeddah'); ?></option>
                        <option value="dammam" <?php echo $location === 'dammam' ? 'selected' : ''; ?>><?php echo __('dammam'); ?></option>
                        <option value="makkah" <?php echo $location === 'makkah' ? 'selected' : ''; ?>><?php echo __('makkah'); ?></option>
                        <option value="madinah" <?php echo $location === 'madinah' ? 'selected' : ''; ?>><?php echo __('madinah'); ?></option>
                    </select>
                </div>
                <div class="search-group">
                    <label for="bedrooms"><?php echo __('bedrooms'); ?></label>
                    <select name="bedrooms" id="bedrooms" class="form-select">
                        <option value=""><?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $bedrooms === $i ? 'selected' : ''; ?>><?php echo $i; ?>+</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="search-group">
                    <label for="guests"><?php echo __('guests'); ?></label>
                    <select name="guests" id="guests" class="form-select">
                        <option value=""><?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $guests === $i ? 'selected' : ''; ?>><?php echo $i; ?>+</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="search-group">
                    <label for="max_price"><?php echo $lang === 'ar' ? 'السعر الأقصى' : 'Max Price'; ?></label>
                    <select name="max_price" id="max_price" class="form-select">
                        <option value=""><?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></option>
                        <option value="300" <?php echo $maxPrice === 300 ? 'selected' : ''; ?>>300 <?php echo $lang === 'ar' ? 'ر.س' : 'SAR'; ?></option>
                        <option value="500" <?php echo $maxPrice === 500 ? 'selected' : ''; ?>>500 <?php echo $lang === 'ar' ? 'ر.س' : 'SAR'; ?></option>
                        <option value="1000" <?php echo $maxPrice === 1000 ? 'selected' : ''; ?>>1000 <?php echo $lang === 'ar' ? 'ر.س' : 'SAR'; ?></option>
                        <option value="2000" <?php echo $maxPrice === 2000 ? 'selected' : ''; ?>>2000 <?php echo $lang === 'ar' ? 'ر.س' : 'SAR'; ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><?php echo __('search'); ?></button>
            </form>
        </div>
        
        <!-- Results Count -->
        <div style="margin-bottom: var(--space-lg); color: var(--dark-gray);">
            <?php echo $lang === 'ar' ? 'تم العثور على' : 'Found'; ?> 
            <strong><?php echo $totalCount; ?></strong> 
            <?php echo $lang === 'ar' ? 'شقة' : 'apartments'; ?>
        </div>
        
        <!-- Apartments Grid -->
        <div class="grid grid-3">
            <?php if (!empty($apartments)): ?>
                <?php foreach ($apartments as $apartment): ?>
                <article class="apartment-card">
                    <div class="apartment-image">
                        <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($apartment['primary_image'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo sanitizeOutput($lang === 'ar' ? $apartment['title_ar'] : $apartment['title_en']); ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&q=80'">
                        <?php if ($apartment['featured']): ?>
                        <span class="apartment-badge"><?php echo $lang === 'ar' ? 'مميز' : 'Featured'; ?></span>
                        <?php endif; ?>
                        <button class="apartment-favorite" onclick="JifApp.Favorites.toggle(<?php echo $apartment['id']; ?>, this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                        </button>
                    </div>
                    <div class="apartment-content">
                        <div class="apartment-location">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><?php echo sanitizeOutput($lang === 'ar' ? $apartment['city_ar'] : $apartment['city_en']); ?></span>
                        </div>
                        <h3 class="apartment-title">
                            <a href="<?php echo BASE_URL; ?>/pages/apartment.php?id=<?php echo $apartment['id']; ?>">
                                <?php echo sanitizeOutput($lang === 'ar' ? $apartment['title_ar'] : $apartment['title_en']); ?>
                            </a>
                        </h3>
                        <div class="apartment-features">
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 012 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>
                                <span><?php echo $apartment['bedrooms']; ?></span>
                            </div>
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l.463-.536a1.964 1.964 0 013.074 0L13 6"/><path d="M8 6v10a4 4 0 004 4h0a4 4 0 004-4V6"/><path d="M5 20h14"/></svg>
                                <span><?php echo $apartment['bathrooms']; ?></span>
                            </div>
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                <span><?php echo $apartment['area_sqm']; ?> <?php echo __('sqm'); ?></span>
                            </div>
                        </div>
                        <div class="apartment-footer">
                            <div class="apartment-price">
                                <?php echo formatPrice($apartment['price_per_day']); ?>
                                <span><?php echo __('per_day'); ?></span>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/pages/apartment.php?id=<?php echo $apartment['id']; ?>" class="btn btn-outline btn-sm">
                                <?php echo __('view_details'); ?>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 3; text-align: center; padding: 60px 20px;">
                    <p style="color: var(--medium-gray); font-size: 1.25rem;"><?php echo __('no_results'); ?></p>
                    <a href="<?php echo BASE_URL; ?>/pages/apartments.php" class="btn btn-outline" style="margin-top: var(--space-lg);">
                        <?php echo $lang === 'ar' ? 'مسح الفلاتر' : 'Clear Filters'; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination && $pagination['total_pages'] > 1): ?>
        <div class="admin-pagination" style="margin-top: var(--space-2xl); background: var(--white); border-radius: var(--radius-lg);">
            <div class="pagination-info">
                <?php echo $lang === 'ar' ? 'صفحة' : 'Page'; ?> <?php echo $pagination['current_page']; ?> 
                <?php echo $lang === 'ar' ? 'من' : 'of'; ?> <?php echo $pagination['total_pages']; ?>
            </div>
            <div class="pagination-buttons">
                <?php if ($pagination['has_prev']): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="pagination-btn">
                    <?php echo $lang === 'ar' ? '→' : '←'; ?>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($pagination['total_pages'], $page + 2); $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="pagination-btn">
                    <?php echo $lang === 'ar' ? '←' : '→'; ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
