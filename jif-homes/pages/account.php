<?php
/**
 * JIF HOMES - User Account Page (Protected)
 */

$pageTitle = 'my_account';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translations.php';

requireLogin();

$lang = getCurrentLanguage();
$errors = [];
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = __('required_field');
        } elseif (strlen($newPassword) < 8) {
            $errors[] = __('password_min_length');
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = __('passwords_not_match');
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && verifyPassword($currentPassword, $user['password'])) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    $success = __('password_changed');
                } else {
                    $errors[] = __('current_password_wrong');
                }
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'حدث خطأ' : 'An error occurred';
            }
        }
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            $errors[] = __('required_field');
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$firstName, $lastName, $phone, $_SESSION['user_id']]);
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                $success = $lang === 'ar' ? 'تم تحديث الملف الشخصي بنجاح' : 'Profile updated successfully';
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'حدث خطأ' : 'An error occurred';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

try {
    $db = getDB();
    
    // Get user info
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Get favorites
    $stmt = $db->prepare("
        SELECT a.*, 
               (SELECT image_path FROM apartment_images WHERE apartment_id = a.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM favorites f
        JOIN apartments a ON f.apartment_id = a.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $user = null;
    $favorites = [];
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('my_account'); ?></h2>
            <div class="section-divider"></div>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom: var(--space-lg);">
            <?php foreach ($errors as $error): ?>
            <div><?php echo sanitizeOutput($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: var(--space-lg);">
            <?php echo sanitizeOutput($success); ?>
        </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: var(--space-2xl);">
            <!-- Sidebar -->
            <div>
                <div class="form-container" style="text-align: center;">
                    <div style="width: 100px; height: 100px; background: var(--desert-brown); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-lg); font-size: 2.5rem; color: var(--white); font-weight: 700;">
                        <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <h3><?php echo sanitizeOutput($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p style="color: var(--text-muted);"><?php echo sanitizeOutput($user['email']); ?></p>
                    <?php if ($user['phone']): ?>
                    <p style="color: var(--text-muted);" dir="ltr"><?php echo sanitizeOutput($user['phone']); ?></p>
                    <?php endif; ?>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: var(--space-lg);">
                        <?php echo $lang === 'ar' ? 'عضو منذ' : 'Member since'; ?> <?php echo formatDate($user['created_at']); ?>
                    </p>
                    
                    <div style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
                        <a href="<?php echo BASE_URL; ?>/pages/logout.php" class="btn btn-outline" style="width: 100%;">
                            <?php echo __('logout'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div>
                <!-- Profile Settings -->
                <div class="form-container" style="margin-bottom: var(--space-xl);">
                    <h3 class="form-section-title"><?php echo $lang === 'ar' ? 'الملف الشخصي' : 'Profile Settings'; ?></h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                            <div class="form-group">
                                <label class="form-label"><?php echo __('first_name'); ?></label>
                                <input type="text" name="first_name" class="form-input" value="<?php echo sanitizeOutput($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?php echo __('last_name'); ?></label>
                                <input type="text" name="last_name" class="form-input" value="<?php echo sanitizeOutput($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('email'); ?></label>
                            <input type="email" class="form-input" value="<?php echo sanitizeOutput($user['email']); ?>" disabled style="background: var(--pearl-white);">
                            <small style="color: var(--text-muted);"><?php echo $lang === 'ar' ? 'لا يمكن تغيير البريد الإلكتروني' : 'Email cannot be changed'; ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('phone'); ?></label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo sanitizeOutput($user['phone']); ?>" dir="ltr">
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?php echo $lang === 'ar' ? 'حفظ التغييرات' : 'Save Changes'; ?></button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="form-container" style="margin-bottom: var(--space-xl);">
                    <h3 class="form-section-title"><?php echo __('change_password'); ?></h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('current_password'); ?></label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('new_password'); ?></label>
                            <input type="password" name="new_password" class="form-input" minlength="8" required>
                            <small style="color: var(--text-muted);"><?php echo __('password_min_length_hint'); ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('confirm_password'); ?></label>
                            <input type="password" name="confirm_password" class="form-input" minlength="8" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?php echo __('change_password'); ?></button>
                    </form>
                </div>
                
                <!-- Favorites -->
                <div class="form-container">
                    <h3 class="form-section-title"><?php echo $lang === 'ar' ? 'المفضلة' : 'Favorites'; ?></h3>
                    
                    <?php if (!empty($favorites)): ?>
                    <div class="grid grid-3" style="gap: var(--space-md);">
                        <?php foreach ($favorites as $fav): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/apartment.php?id=<?php echo $fav['id']; ?>" style="display: block;">
                            <div style="background: var(--pearl-white); border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color);">
                                <img src="<?php echo BASE_URL; ?>/image/apartments/<?php echo sanitizeOutput($fav['primary_image'] ?? 'default.jpg'); ?>" 
                                     style="width: 100%; height: 120px; object-fit: cover;"
                                     onerror="this.src='https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&q=80'">
                                <div style="padding: var(--space-sm);">
                                    <h5 style="font-size: 0.875rem; margin-bottom: var(--space-xs);">
                                        <?php echo sanitizeOutput($lang === 'ar' ? $fav['title_ar'] : $fav['title_en']); ?>
                                    </h5>
                                    <p style="font-size: 0.875rem; color: var(--desert-brown); font-weight: 600; margin: 0;">
                                        <?php echo formatPrice($fav['price_per_day']); ?><?php echo __('per_day'); ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: var(--text-muted); text-align: center; padding: var(--space-xl);">
                        <?php echo $lang === 'ar' ? 'لا توجد شقق في المفضلة' : 'No favorites yet'; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 768px) {
    .container > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
