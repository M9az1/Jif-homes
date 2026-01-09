<?php
/**
 * JIF HOMES - Admin Apartment Form (Add/Edit)
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/translations.php';

requireAdmin();

if (isset($_GET['lang'])) setLanguage($_GET['lang']);
$lang = getCurrentLanguage();
$dir = getDirection();

$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$apartment = null;
$images = [];
$errors = [];

// Get existing apartment data
if ($isEdit) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM apartments WHERE id = ?");
        $stmt->execute([$id]);
        $apartment = $stmt->fetch();
        
        if (!$apartment) {
            header('Location: ' . BASE_URL . '/pages/admin/apartments.php');
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM apartment_images WHERE apartment_id = ? ORDER BY is_primary DESC, sort_order");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        header('Location: ' . BASE_URL . '/pages/admin/apartments.php');
        exit;
    }
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security error';
    } else {
        $data = [
            'title_ar' => sanitizeInput($_POST['title_ar'] ?? ''),
            'title_en' => sanitizeInput($_POST['title_en'] ?? ''),
            'description_ar' => sanitizeInput($_POST['description_ar'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'address_ar' => sanitizeInput($_POST['address_ar'] ?? ''),
            'address_en' => sanitizeInput($_POST['address_en'] ?? ''),
            'city_ar' => sanitizeInput($_POST['city_ar'] ?? ''),
            'city_en' => sanitizeInput($_POST['city_en'] ?? ''),
            'price_per_day' => (float)($_POST['price_per_day'] ?? 0),
            'bedrooms' => (int)($_POST['bedrooms'] ?? 1),
            'bathrooms' => (int)($_POST['bathrooms'] ?? 1),
            'area_sqm' => (float)($_POST['area_sqm'] ?? 0),
            'max_guests' => (int)($_POST['max_guests'] ?? 2),
            'floor_number' => (int)($_POST['floor_number'] ?? 0),
            'has_wifi' => isset($_POST['has_wifi']) ? 1 : 0,
            'has_ac' => isset($_POST['has_ac']) ? 1 : 0,
            'has_kitchen' => isset($_POST['has_kitchen']) ? 1 : 0,
            'has_parking' => isset($_POST['has_parking']) ? 1 : 0,
            'has_tv' => isset($_POST['has_tv']) ? 1 : 0,
            'has_washer' => isset($_POST['has_washer']) ? 1 : 0,
            'has_pool' => isset($_POST['has_pool']) ? 1 : 0,
            'has_gym' => isset($_POST['has_gym']) ? 1 : 0,
            'latitude' => (float)($_POST['latitude'] ?? 0),
            'longitude' => (float)($_POST['longitude'] ?? 0),
            'status' => sanitizeInput($_POST['status'] ?? 'active'),
            'featured' => isset($_POST['featured']) ? 1 : 0,
        ];
        
        // Validation
        if (empty($data['title_ar'])) $errors[] = 'Arabic title is required';
        if (empty($data['title_en'])) $errors[] = 'English title is required';
        if ($data['price_per_day'] <= 0) $errors[] = 'Valid price is required';
        
        if (empty($errors)) {
            try {
                $db = getDB();
                
                if ($isEdit) {
                    $sql = "UPDATE apartments SET 
                            title_ar = ?, title_en = ?, description_ar = ?, description_en = ?,
                            address_ar = ?, address_en = ?, city_ar = ?, city_en = ?,
                            price_per_day = ?, bedrooms = ?, bathrooms = ?, area_sqm = ?,
                            max_guests = ?, floor_number = ?, has_wifi = ?, has_ac = ?,
                            has_kitchen = ?, has_parking = ?, has_tv = ?, has_washer = ?,
                            has_pool = ?, has_gym = ?, latitude = ?, longitude = ?,
                            status = ?, featured = ?, updated_at = NOW()
                            WHERE id = ?";
                    $params = array_values($data);
                    $params[] = $id;
                } else {
                    $sql = "INSERT INTO apartments (
                            title_ar, title_en, description_ar, description_en,
                            address_ar, address_en, city_ar, city_en,
                            price_per_day, bedrooms, bathrooms, area_sqm,
                            max_guests, floor_number, has_wifi, has_ac,
                            has_kitchen, has_parking, has_tv, has_washer,
                            has_pool, has_gym, latitude, longitude,
                            status, featured, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $params = array_values($data);
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                $apartmentId = $isEdit ? $id : $db->lastInsertId();
                
                // Also create location entry if coordinates provided
                if ($data['latitude'] && $data['longitude']) {
                    $stmt = $db->prepare("SELECT id FROM locations WHERE apartment_id = ?");
                    $stmt->execute([$apartmentId]);
                    $existingLocation = $stmt->fetch();
                    
                    if ($existingLocation) {
                        $db->prepare("UPDATE locations SET name_ar = ?, name_en = ?, latitude = ?, longitude = ? WHERE apartment_id = ?")
                           ->execute([$data['title_ar'], $data['title_en'], $data['latitude'], $data['longitude'], $apartmentId]);
                    } else {
                        $db->prepare("INSERT INTO locations (apartment_id, name_ar, name_en, latitude, longitude, marker_type) VALUES (?, ?, ?, ?, ?, 'apartment')")
                           ->execute([$apartmentId, $data['title_ar'], $data['title_en'], $data['latitude'], $data['longitude']]);
                    }
                }
                
                setFlashMessage('success', $isEdit ? 'Apartment updated' : 'Apartment created');
                header('Location: ' . BASE_URL . '/pages/admin/apartments.php');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
        
        $apartment = $data;
    }
}

$pageTitle = $isEdit ? __('edit_apartment') : __('add_apartment');
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <ul style="margin: 0; padding-inline-start: 20px;">
        <?php foreach ($errors as $error): ?>
        <li><?php echo sanitizeOutput($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="" method="POST" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="admin-card" style="margin-bottom: var(--space-xl);">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'المعلومات الأساسية' : 'Basic Information'; ?></h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'العنوان (عربي)' : 'Title (Arabic)'; ?> *</label>
                    <input type="text" name="title_ar" class="form-input" value="<?php echo sanitizeOutput($apartment['title_ar'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'العنوان (إنجليزي)' : 'Title (English)'; ?> *</label>
                    <input type="text" name="title_en" class="form-input" value="<?php echo sanitizeOutput($apartment['title_en'] ?? ''); ?>" required>
                </div>
                <div class="form-group admin-form-full">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الوصف (عربي)' : 'Description (Arabic)'; ?></label>
                    <textarea name="description_ar" class="form-textarea" rows="4"><?php echo sanitizeOutput($apartment['description_ar'] ?? ''); ?></textarea>
                </div>
                <div class="form-group admin-form-full">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الوصف (إنجليزي)' : 'Description (English)'; ?></label>
                    <textarea name="description_en" class="form-textarea" rows="4"><?php echo sanitizeOutput($apartment['description_en'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card" style="margin-bottom: var(--space-xl);">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'الموقع' : 'Location'; ?></h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'العنوان (عربي)' : 'Address (Arabic)'; ?></label>
                    <input type="text" name="address_ar" class="form-input" value="<?php echo sanitizeOutput($apartment['address_ar'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'العنوان (إنجليزي)' : 'Address (English)'; ?></label>
                    <input type="text" name="address_en" class="form-input" value="<?php echo sanitizeOutput($apartment['address_en'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'المدينة (عربي)' : 'City (Arabic)'; ?></label>
                    <input type="text" name="city_ar" class="form-input" value="<?php echo sanitizeOutput($apartment['city_ar'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'المدينة (إنجليزي)' : 'City (English)'; ?></label>
                    <input type="text" name="city_en" class="form-input" value="<?php echo sanitizeOutput($apartment['city_en'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'خط العرض' : 'Latitude'; ?></label>
                    <input type="number" name="latitude" id="latitude" class="form-input" step="any" value="<?php echo $apartment['latitude'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'خط الطول' : 'Longitude'; ?></label>
                    <input type="number" name="longitude" id="longitude" class="form-input" step="any" value="<?php echo $apartment['longitude'] ?? ''; ?>">
                </div>
                <div class="form-group admin-form-full">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'انقر على الخريطة لتحديد الموقع' : 'Click on map to set location'; ?></label>
                    <div class="map-admin-container" id="locationMap"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card" style="margin-bottom: var(--space-xl);">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'التفاصيل والسعر' : 'Details & Pricing'; ?></h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'السعر لليوم' : 'Price per Day'; ?> (SAR) *</label>
                    <input type="number" name="price_per_day" class="form-input" value="<?php echo $apartment['price_per_day'] ?? ''; ?>" required min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('bedrooms'); ?></label>
                    <input type="number" name="bedrooms" class="form-input" value="<?php echo $apartment['bedrooms'] ?? 1; ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('bathrooms'); ?></label>
                    <input type="number" name="bathrooms" class="form-input" value="<?php echo $apartment['bathrooms'] ?? 1; ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('area'); ?> (<?php echo __('sqm'); ?>)</label>
                    <input type="number" name="area_sqm" class="form-input" value="<?php echo $apartment['area_sqm'] ?? ''; ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo __('max_guests'); ?></label>
                    <input type="number" name="max_guests" class="form-input" value="<?php echo $apartment['max_guests'] ?? 2; ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'رقم الطابق' : 'Floor Number'; ?></label>
                    <input type="number" name="floor_number" class="form-input" value="<?php echo $apartment['floor_number'] ?? ''; ?>">
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card" style="margin-bottom: var(--space-xl);">
        <div class="admin-card-header">
            <h3><?php echo __('amenities'); ?></h3>
        </div>
        <div class="admin-card-body">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-md);">
                <label class="form-check">
                    <input type="checkbox" name="has_wifi" <?php echo ($apartment['has_wifi'] ?? 0) ? 'checked' : ''; ?>>
                    <span>📶 <?php echo __('wifi'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_ac" <?php echo ($apartment['has_ac'] ?? 1) ? 'checked' : ''; ?>>
                    <span>❄️ <?php echo __('air_conditioning'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_kitchen" <?php echo ($apartment['has_kitchen'] ?? 0) ? 'checked' : ''; ?>>
                    <span>🍳 <?php echo __('kitchen'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_parking" <?php echo ($apartment['has_parking'] ?? 0) ? 'checked' : ''; ?>>
                    <span>🚗 <?php echo __('parking'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_tv" <?php echo ($apartment['has_tv'] ?? 0) ? 'checked' : ''; ?>>
                    <span>📺 <?php echo __('tv'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_washer" <?php echo ($apartment['has_washer'] ?? 0) ? 'checked' : ''; ?>>
                    <span>🧺 <?php echo __('washer'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_pool" <?php echo ($apartment['has_pool'] ?? 0) ? 'checked' : ''; ?>>
                    <span>🏊 <?php echo __('pool'); ?></span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="has_gym" <?php echo ($apartment['has_gym'] ?? 0) ? 'checked' : ''; ?>>
                    <span>💪 <?php echo __('gym'); ?></span>
                </label>
            </div>
        </div>
    </div>
    
    <div class="admin-card" style="margin-bottom: var(--space-xl);">
        <div class="admin-card-header">
            <h3><?php echo __('status'); ?></h3>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid">
                <div class="form-group">
                    <label class="form-label"><?php echo __('status'); ?></label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo ($apartment['status'] ?? '') === 'active' ? 'selected' : ''; ?>><?php echo __('active'); ?></option>
                        <option value="inactive" <?php echo ($apartment['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo __('inactive'); ?></option>
                        <option value="maintenance" <?php echo ($apartment['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>><?php echo $lang === 'ar' ? 'صيانة' : 'Maintenance'; ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-check" style="margin-top: 30px;">
                        <input type="checkbox" name="featured" <?php echo ($apartment['featured'] ?? 0) ? 'checked' : ''; ?>>
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg> <?php echo $lang === 'ar' ? 'شقة مميزة' : 'Featured Apartment'; ?></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: var(--space-md); justify-content: flex-end;">
        <a href="<?php echo BASE_URL; ?>/pages/admin/apartments.php" class="btn btn-outline"><?php echo __('cancel'); ?></a>
        <button type="submit" class="btn btn-primary"><?php echo __('save'); ?></button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = JifApp.MapHandler.init('locationMap', {
        center: [<?php echo $apartment['latitude'] ?? 24.7136; ?>, <?php echo $apartment['longitude'] ?? 46.6753; ?>],
        zoom: <?php echo ($apartment['latitude'] ?? false) ? 15 : 6; ?>
    });
    
    if (map) {
        <?php if ($apartment['latitude'] ?? false): ?>
        JifApp.MapHandler.addMarker(<?php echo $apartment['latitude']; ?>, <?php echo $apartment['longitude']; ?>);
        <?php endif; ?>
        
        map.on('click', function(e) {
            JifApp.MapHandler.clearMarkers();
            JifApp.MapHandler.addMarker(e.latlng.lat, e.latlng.lng);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
