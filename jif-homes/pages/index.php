<?php
/**
 * JIF HOMES - Home Page
 */

$pageTitle = null;
require_once __DIR__ . '/../includes/header.php';

// Get featured apartments from database
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.*, 
               (SELECT image_path FROM apartment_images WHERE apartment_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM apartments a 
        WHERE a.status = 'active' AND a.featured = 1 
        ORDER BY a.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featuredApartments = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredApartments = [];
}

// Get locations for map
try {
    $stmt = $db->prepare("SELECT * FROM locations ORDER BY marker_type, name_" . $lang);
    $stmt->execute();
    $locations = $stmt->fetchAll();
} catch (PDOException $e) {
    $locations = [];
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>
                    <?php echo __('hero_title'); ?>
                    <span><?php echo __('hero_subtitle'); ?></span>
                </h1>
                <p><?php echo __('hero_description'); ?></p>
                <div class="hero-buttons">
                    <a href="<?php echo BASE_URL; ?>/pages/apartments.php" class="btn btn-primary btn-lg"><?php echo __('browse_apartments'); ?></a>
                    <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn btn-secondary btn-lg"><?php echo __('learn_more'); ?></a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number" data-counter="500">0</div>
                        <div class="stat-label"><?php echo __('happy_customers'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-counter="50">0</div>
                        <div class="stat-label"><?php echo __('apartments_available'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-counter="5">0</div>
                        <div class="stat-label"><?php echo __('cities_covered'); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-counter="10">0</div>
                        <div class="stat-label"><?php echo __('years_experience'); ?></div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-main">
                    <img src="<?php echo BASE_URL; ?>/image/hero-apartment.jpg" alt="<?php echo __('site_name'); ?>" onerror="this.src='https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&q=80'">
                </div>
                <div class="hero-floating-card card-1">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--desert-brown)" stroke="var(--desert-brown)" stroke-width="2"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
                    <strong style="color: var(--desert-brown);"> 4.9</strong>
                    <p style="font-size: 0.875rem; margin: 0;"><?php echo $lang === 'ar' ? 'تقييم العملاء' : 'Customer Rating'; ?></p>
                </div>
                <div class="hero-floating-card card-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--hills-green)" stroke-width="3"><polyline points="20,6 9,17 4,12"/></svg>
                    <p style="font-size: 0.875rem; margin: 0;"><?php echo $lang === 'ar' ? 'جودة مضمونة' : 'Quality Guaranteed'; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="container">
    <div class="search-section">
        <form action="<?php echo BASE_URL; ?>/pages/apartments.php" method="GET" class="search-form" id="searchForm">
            <div class="search-group">
                <label for="location"><?php echo __('location'); ?></label>
                <select name="location" id="location" class="form-select">
                    <option value=""><?php echo __('select_city'); ?></option>
                    <option value="riyadh"><?php echo __('riyadh'); ?></option>
                    <option value="jeddah"><?php echo __('jeddah'); ?></option>
                    <option value="dammam"><?php echo __('dammam'); ?></option>
                    <option value="makkah"><?php echo __('makkah'); ?></option>
                    <option value="madinah"><?php echo __('madinah'); ?></option>
                </select>
            </div>
            <div class="search-group">
                <label for="bedrooms"><?php echo __('bedrooms'); ?></label>
                <select name="bedrooms" id="bedrooms" class="form-select">
                    <option value=""><?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?>+</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="search-group">
                <label for="guests"><?php echo __('guests'); ?></label>
                <select name="guests" id="guests" class="form-select">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? __('guest') : __('guests'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><?php echo __('search'); ?></button>
        </form>
    </div>
</section>

<!-- Featured Apartments Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('featured_apartments'); ?></h2>
            <div class="section-divider"></div>
            <p><?php echo __('featured_apartments_desc'); ?></p>
        </div>
        
        <div class="grid grid-3">
            <?php if (!empty($featuredApartments)): ?>
                <?php foreach ($featuredApartments as $apartment): ?>
                <article class="apartment-card">
                    <div class="apartment-image">
                        <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($apartment['primary_image'] ?? 'default.jpg'); ?>" 
                             alt="<?php echo sanitizeOutput($lang === 'ar' ? $apartment['title_ar'] : $apartment['title_en']); ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&q=80'">
                        <?php if ($apartment['featured']): ?>
                        <span class="apartment-badge"><?php echo $lang === 'ar' ? 'مميز' : 'Featured'; ?></span>
                        <?php endif; ?>
                        <button class="apartment-favorite" onclick="JifApp.Favorites.toggle(<?php echo $apartment['id']; ?>, this)" aria-label="<?php echo __('add_to_favorites'); ?>">
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
                                <span><?php echo $apartment['bedrooms']; ?> <?php echo __('bedrooms'); ?></span>
                            </div>
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l.463-.536a1.964 1.964 0 013.074 0L13 6"/><path d="M8 6v10a4 4 0 004 4h0a4 4 0 004-4V6"/><path d="M5 20h14"/></svg>
                                <span><?php echo $apartment['bathrooms']; ?> <?php echo __('bathrooms'); ?></span>
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
                    <p style="color: var(--medium-gray);"><?php echo __('no_results'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/pages/apartments.php" class="btn btn-primary btn-lg"><?php echo __('view_all'); ?></a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('why_choose_us'); ?></h2>
            <div class="section-divider"></div>
            <p><?php echo __('why_choose_us_desc'); ?></p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                </div>
                <h4><?php echo __('feature_1_title'); ?></h4>
                <p><?php echo __('feature_1_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h4><?php echo __('feature_2_title'); ?></h4>
                <p><?php echo __('feature_2_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                </div>
                <h4><?php echo __('feature_3_title'); ?></h4>
                <p><?php echo __('feature_3_desc'); ?></p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <h4><?php echo __('feature_4_title'); ?></h4>
                <p><?php echo __('feature_4_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Locations Map Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('our_locations'); ?></h2>
            <div class="section-divider"></div>
            <p><?php echo __('map_description'); ?></p>
        </div>
        
        <div class="map-container">
            <div id="homeMap" style="width: 100%; height: 100%;"></div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    var map = JifApp.MapHandler.init('homeMap', {
        center: [24.7136, 46.6753],
        zoom: 6
    });
    
    if (map) {
        var locations = <?php echo json_encode($locations); ?>;
        var lang = '<?php echo $lang; ?>';
        var baseUrl = '<?php echo BASE_URL; ?>';
        
        locations.forEach(function(location) {
            var name = lang === 'ar' ? location.name_ar : location.name_en;
            var desc = lang === 'ar' ? location.description_ar : location.description_en;
            
            var popupContent = '<strong>' + name + '</strong>';
            if (desc) popupContent += '<br><small>' + desc + '</small>';
            if (location.apartment_id) {
                popupContent += '<br><a href="' + baseUrl + '/pages/apartment.php?id=' + location.apartment_id + '" class="btn btn-sm btn-primary" style="margin-top: 8px;"><?php echo __('view_details'); ?></a>';
            }
            
            JifApp.MapHandler.addMarker(location.latitude, location.longitude, {
                popup: popupContent
            });
        });
        
        if (locations.length > 1) {
            JifApp.MapHandler.fitBounds();
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
