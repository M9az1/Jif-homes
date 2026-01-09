<?php
/**
 * JIF HOMES - Apartment Detail Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translations.php';

requireLogin();

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$apartmentId = (int)($_GET['id'] ?? 0);

if (!$apartmentId) {
    header('Location: ' . BASE_URL . '/pages/apartments.php');
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM apartments WHERE id = ? AND status = 'active'");
    $stmt->execute([$apartmentId]);
    $apartment = $stmt->fetch();
    
    if (!$apartment) {
        header('Location: ' . BASE_URL . '/pages/apartments.php');
        exit;
    }
    
    $db->prepare("UPDATE apartments SET view_count = view_count + 1 WHERE id = ?")->execute([$apartmentId]);
    
    $stmt = $db->prepare("SELECT * FROM apartment_images WHERE apartment_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $stmt->execute([$apartmentId]);
    $images = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . '/pages/apartments.php');
    exit;
}

$pageTitle = $lang === 'ar' ? $apartment['title_ar'] : $apartment['title_en'];
require_once __DIR__ . '/../includes/header.php';
?>

<section class="apartment-detail">
    <div class="container">
        <!-- Horizontal Gallery -->
        <div class="gallery-horizontal">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $index => $image): ?>
                <div class="gallery-horizontal-item">
                    <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($image['image_path']); ?>" 
                         alt="<?php echo sanitizeOutput($pageTitle); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80'">
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="gallery-horizontal-item">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80" alt="<?php echo sanitizeOutput($pageTitle); ?>">
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Apartment Info -->
        <div class="apartment-info-grid">
            <div class="apartment-main-info">
                <h1><?php echo sanitizeOutput($pageTitle); ?></h1>
                
                <div class="apartment-meta">
                    <div class="meta-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?php echo sanitizeOutput($lang === 'ar' ? $apartment['address_ar'] : $apartment['address_en']); ?>, <?php echo sanitizeOutput($lang === 'ar' ? $apartment['city_ar'] : $apartment['city_en']); ?></span>
                    </div>
                    <div class="meta-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span><?php echo number_format($apartment['view_count']); ?> <?php echo $lang === 'ar' ? 'مشاهدة' : 'views'; ?></span>
                    </div>
                </div>
                
                <!-- Details Grid -->
                <div class="details-grid">
                    <div class="detail-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 012 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>
                        <div class="value"><?php echo $apartment['bedrooms']; ?></div>
                        <div class="label"><?php echo __('bedrooms'); ?></div>
                    </div>
                    <div class="detail-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l.463-.536a1.964 1.964 0 013.074 0L13 6"/><path d="M8 6v10a4 4 0 004 4h0a4 4 0 004-4V6"/><path d="M5 20h14"/></svg>
                        <div class="value"><?php echo $apartment['bathrooms']; ?></div>
                        <div class="label"><?php echo __('bathrooms'); ?></div>
                    </div>
                    <div class="detail-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <div class="value"><?php echo $apartment['area_sqm']; ?></div>
                        <div class="label"><?php echo __('sqm'); ?></div>
                    </div>
                    <div class="detail-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        <div class="value"><?php echo $apartment['max_guests']; ?></div>
                        <div class="label"><?php echo __('max_guests'); ?></div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="apartment-description">
                    <h3><?php echo $lang === 'ar' ? 'الوصف' : 'Description'; ?></h3>
                    <p><?php echo nl2br(sanitizeOutput($lang === 'ar' ? $apartment['description_ar'] : $apartment['description_en'])); ?></p>
                </div>
                
                <!-- Amenities -->
                <div class="apartment-amenities">
                    <h3><?php echo __('amenities'); ?></h3>
                    <div class="amenities-grid">
                        <?php if ($apartment['has_wifi']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                            <span><?php echo __('wifi'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_ac']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v10"/><path d="M18.4 6.6L12 12 5.6 6.6"/><path d="M4 14a8 8 0 0016 0"/></svg>
                            <span><?php echo __('air_conditioning'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_kitchen']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 002-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 00-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                            <span><?php echo __('kitchen'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_parking']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h4a3 3 0 010 6H9"/></svg>
                            <span><?php echo __('parking'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_tv']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17,2 12,7 7,2"/></svg>
                            <span><?php echo __('tv'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_washer']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><circle cx="12" cy="13" r="5"/><path d="M12 8v2"/></svg>
                            <span><?php echo __('washer'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_pool']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20"/><path d="M2 16c2.5 2 5 2 7.5 0s5-2 7.5 0 5 2 7.5 0"/><path d="M2 20c2.5 2 5 2 7.5 0s5-2 7.5 0"/></svg>
                            <span><?php echo __('pool'); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($apartment['has_gym']): ?>
                        <div class="amenity-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6.5 6.5H17.5V17.5H6.5z"/><path d="M22 12H2"/><path d="M12 2v20"/></svg>
                            <span><?php echo __('gym'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Map -->
                <?php if ($apartment['latitude'] && $apartment['longitude']): ?>
                <div style="margin-top: 2rem;">
                    <h3><?php echo __('location'); ?></h3>
                    <div class="map-container">
                        <div id="apartment-map"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Contact Sidebar -->
            <div class="booking-sidebar">
                <div class="booking-price">
                    <?php echo formatPrice($apartment['price_per_day']); ?>
                    <span class="period"><?php echo __('per_day'); ?></span>
                </div>
                
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 1.5rem;">
                    <?php echo $lang === 'ar' ? 'للحجز والاستفسار تواصل معنا' : 'Contact us for booking'; ?>
                </p>
                
                <a href="https://wa.me/966548224343?text=<?php echo urlencode($lang === 'ar' ? 'مرحبا، أريد الاستفسار عن: ' . $pageTitle : 'Hello, I want to inquire about: ' . $pageTitle); ?>" 
                   target="_blank" 
                   class="btn btn-green btn-lg" 
                   style="width: 100%; margin-bottom: 1rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <?php echo $lang === 'ar' ? 'تواصل عبر واتساب' : 'Contact via WhatsApp'; ?>
                </a>
                
                <div style="display: flex; gap: 0.5rem;">
                    <a href="https://www.instagram.com/jif.homes/" target="_blank" class="btn btn-outline" style="flex: 1;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@jif.homes/" target="_blank" class="btn btn-outline" style="flex: 1;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                    </a>
                </div>
                
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <button class="btn btn-outline" style="width: 100%;" onclick="JifApp.Favorites.toggle(<?php echo $apartment['id']; ?>, this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                        <?php echo __('add_to_favorites'); ?>
                    </button>
                </div>
                
                <div style="margin-top: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                    <p><?php echo $lang === 'ar' ? 'للاستفسار المباشر:' : 'Direct inquiries:'; ?></p>
                    <p dir="ltr" style="font-weight: 600; color: var(--desert-brown);">+966 54 822 4343</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($apartment['latitude'] && $apartment['longitude']): ?>
    if (typeof L !== 'undefined') {
        const map = L.map('apartment-map').setView([<?php echo $apartment['latitude']; ?>, <?php echo $apartment['longitude']; ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        L.marker([<?php echo $apartment['latitude']; ?>, <?php echo $apartment['longitude']; ?>])
            .addTo(map)
            .bindPopup('<strong><?php echo addslashes($pageTitle); ?></strong>')
            .openPopup();
    }
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
