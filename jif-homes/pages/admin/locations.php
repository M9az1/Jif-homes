<?php
/**
 * JIF HOMES - Admin Locations Management
 */

$pageTitle = 'manage_locations';
require_once __DIR__ . '/../../includes/admin_header.php';

// Handle actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// Delete location
if ($action === 'delete' && $id > 0) {
    if (validateCSRFToken($_GET['token'] ?? '')) {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM locations WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', $lang === 'ar' ? 'تم حذف الموقع' : 'Location deleted');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Error');
        }
    }
    header('Location: ' . BASE_URL . '/pages/admin/locations.php');
    exit;
}

// Add/Edit location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $data = [
            'name_ar' => sanitizeInput($_POST['name_ar'] ?? ''),
            'name_en' => sanitizeInput($_POST['name_en'] ?? ''),
            'description_ar' => sanitizeInput($_POST['description_ar'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'latitude' => (float)($_POST['latitude'] ?? 0),
            'longitude' => (float)($_POST['longitude'] ?? 0),
            'marker_type' => sanitizeInput($_POST['marker_type'] ?? 'landmark'),
            'apartment_id' => ($_POST['apartment_id'] ?? '') ?: null,
        ];
        
        if (!empty($data['name_ar']) && !empty($data['name_en']) && $data['latitude'] && $data['longitude']) {
            try {
                $db = getDB();
                
                if ($action === 'edit' && $id > 0) {
                    $stmt = $db->prepare("UPDATE locations SET name_ar=?, name_en=?, description_ar=?, description_en=?, latitude=?, longitude=?, marker_type=?, apartment_id=? WHERE id=?");
                    $stmt->execute([$data['name_ar'], $data['name_en'], $data['description_ar'], $data['description_en'], $data['latitude'], $data['longitude'], $data['marker_type'], $data['apartment_id'], $id]);
                } else {
                    $stmt = $db->prepare("INSERT INTO locations (name_ar, name_en, description_ar, description_en, latitude, longitude, marker_type, apartment_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$data['name_ar'], $data['name_en'], $data['description_ar'], $data['description_en'], $data['latitude'], $data['longitude'], $data['marker_type'], $data['apartment_id']]);
                }
                
                setFlashMessage('success', $action === 'edit' ? ($lang === 'ar' ? 'تم التحديث' : 'Updated') : ($lang === 'ar' ? 'تمت الإضافة' : 'Added'));
            } catch (PDOException $e) {
                setFlashMessage('error', 'Error');
            }
        }
    }
    header('Location: ' . BASE_URL . '/pages/admin/locations.php');
    exit;
}

// Get locations
try {
    $db = getDB();
    $locations = $db->query("
        SELECT l.*, a.title_ar as apartment_title_ar, a.title_en as apartment_title_en 
        FROM locations l 
        LEFT JOIN apartments a ON l.apartment_id = a.id 
        ORDER BY l.marker_type, l.name_" . $lang
    )->fetchAll();
    
    $apartments = $db->query("SELECT id, title_ar, title_en FROM apartments WHERE status = 'active' ORDER BY title_" . $lang)->fetchAll();
} catch (PDOException $e) {
    $locations = [];
    $apartments = [];
}
?>

<div class="admin-content-grid">
    <!-- Map -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><?php echo $lang === 'ar' ? 'خريطة المواقع' : 'Locations Map'; ?></h3>
        </div>
        <div class="admin-card-body">
            <div class="map-instructions">
                <?php echo $lang === 'ar' ? 'انقر على الخريطة لإضافة موقع جديد أو تحديث الإحداثيات' : 'Click on the map to add a new location or update coordinates'; ?>
            </div>
            <div class="map-admin-container" id="adminMap"></div>
        </div>
    </div>
    
    <!-- Add/Edit Form -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 id="formTitle"><?php echo __('add_location'); ?></h3>
        </div>
        <div class="admin-card-body">
            <form action="" method="POST" id="locationForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="locationId" value="">
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)'; ?> *</label>
                    <input type="text" name="name_ar" id="name_ar" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)'; ?> *</label>
                    <input type="text" name="name_en" id="name_en" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الوصف (عربي)' : 'Description (Arabic)'; ?></label>
                    <textarea name="description_ar" id="description_ar" class="form-textarea" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'الوصف (إنجليزي)' : 'Description (English)'; ?></label>
                    <textarea name="description_en" id="description_en" class="form-textarea" rows="2"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'خط العرض' : 'Latitude'; ?> *</label>
                        <input type="number" name="latitude" id="latitude" class="form-input" step="any" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php echo $lang === 'ar' ? 'خط الطول' : 'Longitude'; ?> *</label>
                        <input type="number" name="longitude" id="longitude" class="form-input" step="any" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'نوع الموقع' : 'Location Type'; ?></label>
                    <select name="marker_type" id="marker_type" class="form-select">
                        <option value="apartment"><?php echo $lang === 'ar' ? 'شقة' : 'Apartment'; ?></option>
                        <option value="landmark"><?php echo $lang === 'ar' ? 'معلم' : 'Landmark'; ?></option>
                        <option value="office"><?php echo $lang === 'ar' ? 'مكتب' : 'Office'; ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $lang === 'ar' ? 'ربط بشقة' : 'Link to Apartment'; ?></label>
                    <select name="apartment_id" id="apartment_id" class="form-select">
                        <option value=""><?php echo $lang === 'ar' ? 'بدون' : 'None'; ?></option>
                        <?php foreach ($apartments as $apt): ?>
                        <option value="<?php echo $apt['id']; ?>"><?php echo sanitizeOutput($lang === 'ar' ? $apt['title_ar'] : $apt['title_en']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; gap: var(--space-md);">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><?php echo __('save'); ?></button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()"><?php echo __('cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Locations List -->
<div class="admin-card" style="margin-top: var(--space-xl);">
    <div class="admin-card-header">
        <h3><?php echo $lang === 'ar' ? 'قائمة المواقع' : 'Locations List'; ?> (<?php echo count($locations); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th><?php echo $lang === 'ar' ? 'الاسم' : 'Name'; ?></th>
                <th><?php echo $lang === 'ar' ? 'النوع' : 'Type'; ?></th>
                <th><?php echo $lang === 'ar' ? 'الشقة المرتبطة' : 'Linked Apartment'; ?></th>
                <th><?php echo $lang === 'ar' ? 'الإحداثيات' : 'Coordinates'; ?></th>
                <th><?php echo __('actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($locations)): ?>
                <?php foreach ($locations as $location): ?>
                <tr>
                    <td>
                        <strong><?php echo sanitizeOutput($lang === 'ar' ? $location['name_ar'] : $location['name_en']); ?></strong>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $location['marker_type'] === 'apartment' ? 'active' : ($location['marker_type'] === 'office' ? 'pending' : ''); ?>">
                            <?php 
                            $types = ['apartment' => $lang === 'ar' ? 'شقة' : 'Apartment', 'landmark' => $lang === 'ar' ? 'معلم' : 'Landmark', 'office' => $lang === 'ar' ? 'مكتب' : 'Office'];
                            echo $types[$location['marker_type']] ?? $location['marker_type'];
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($location['apartment_id']): ?>
                        <?php echo sanitizeOutput($lang === 'ar' ? $location['apartment_title_ar'] : $location['apartment_title_en']); ?>
                        <?php else: ?>
                        <span style="color: var(--medium-gray);">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-family: monospace; font-size: 0.8rem;">
                        <?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn view" onclick="focusLocation(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></button>
                            <button class="action-btn edit" onclick="editLocation(<?php echo htmlspecialchars(json_encode($location)); ?>)"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                            <a href="?action=delete&id=<?php echo $location['id']; ?>&token=<?php echo generateCSRFToken(); ?>" 
                               class="action-btn delete" 
                               onclick="return confirmDelete();"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/></svg></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: var(--space-2xl); color: var(--medium-gray);">
                        <?php echo __('no_results'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
let map, currentMarker;

document.addEventListener('DOMContentLoaded', function() {
    map = JifApp.MapHandler.init('adminMap', {
        center: [24.7136, 46.6753],
        zoom: 6
    });
    
    if (map) {
        // Add existing markers
        const locations = <?php echo json_encode($locations); ?>;
        const lang = '<?php echo $lang; ?>';
        
        locations.forEach(location => {
            const name = lang === 'ar' ? location.name_ar : location.name_en;
            JifApp.MapHandler.addMarker(location.latitude, location.longitude, {
                popup: `<strong>${name}</strong><br><small>${location.marker_type}</small>`
            });
        });
        
        if (locations.length > 1) {
            JifApp.MapHandler.fitBounds();
        }
        
        // Click to set coordinates
        map.on('click', function(e) {
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            currentMarker = L.marker(e.latlng, {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background: #C9A227; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>'
                })
            }).addTo(map);
            
            document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
        });
    }
});

function focusLocation(lat, lng) {
    map.setView([lat, lng], 15);
}

function editLocation(location) {
    document.getElementById('formTitle').textContent = '<?php echo __("edit_location"); ?>';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('locationId').value = location.id;
    document.getElementById('name_ar').value = location.name_ar;
    document.getElementById('name_en').value = location.name_en;
    document.getElementById('description_ar').value = location.description_ar || '';
    document.getElementById('description_en').value = location.description_en || '';
    document.getElementById('latitude').value = location.latitude;
    document.getElementById('longitude').value = location.longitude;
    document.getElementById('marker_type').value = location.marker_type;
    document.getElementById('apartment_id').value = location.apartment_id || '';
    
    focusLocation(location.latitude, location.longitude);
    
    document.getElementById('locationForm').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('formTitle').textContent = '<?php echo __("add_location"); ?>';
    document.getElementById('formAction').value = 'add';
    document.getElementById('locationId').value = '';
    document.getElementById('locationForm').reset();
    
    if (currentMarker) {
        map.removeLayer(currentMarker);
        currentMarker = null;
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
