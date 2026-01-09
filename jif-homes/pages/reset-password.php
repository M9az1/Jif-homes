<?php
/**
 * JIF HOMES - Reset Password Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translations.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/account.php');
    exit;
}

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$dir = getDirection();

$errors = [];
$success = false;
$validToken = false;
$token = $_GET['token'] ?? '';

// Verify token
if (!empty($token)) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, first_name FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $validToken = true;
        }
    } catch (PDOException $e) {
        $validToken = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password)) {
            $errors[] = __('required_field');
        } elseif (strlen($password) < 8) {
            $errors[] = __('password_min_length');
        } elseif ($password !== $confirmPassword) {
            $errors[] = __('passwords_not_match');
        } else {
            try {
                $db = getDB();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
                $stmt->execute([$hashedPassword, $token]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'حدث خطأ' : 'An error occurred';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo __('reset_password'); ?> - <?php echo __('site_name'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-image">
                <img src="<?php echo BASE_URL; ?>/image/logo-white.png" alt="JIF HOMES">
                <h2><?php echo __('site_tagline'); ?></h2>
                <p style="margin-top: 1rem; opacity: 0.9;"><?php echo $lang === 'ar' ? 'إنشاء كلمة مرور جديدة لحسابك' : 'Create a new password for your account'; ?></p>
            </div>
            
            <div class="auth-form">
                <div class="lang-switcher" style="margin-bottom: 2rem;">
                    <a href="?token=<?php echo urlencode($token); ?>&lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">العربية</a>
                    <span class="divider">|</span>
                    <a href="?token=<?php echo urlencode($token); ?>&lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                
                <?php if ($success): ?>
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; background: #E8F5E9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                        </div>
                        <h2><?php echo $lang === 'ar' ? 'تم بنجاح!' : 'Success!'; ?></h2>
                        <p class="subtitle"><?php echo __('password_reset_success'); ?></p>
                        <a href="<?php echo BASE_URL; ?>/pages/login.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1.5rem;">
                            <?php echo __('login'); ?>
                        </a>
                    </div>
                
                <?php elseif (!$validToken): ?>
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; background: #FFEBEE; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C62828" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <h2><?php echo $lang === 'ar' ? 'رابط غير صالح' : 'Invalid Link'; ?></h2>
                        <p class="subtitle"><?php echo __('invalid_reset_token'); ?></p>
                        <a href="<?php echo BASE_URL; ?>/pages/forgot-password.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1.5rem;">
                            <?php echo __('request_new_link'); ?>
                        </a>
                    </div>
                
                <?php else: ?>
                    <h2><?php echo __('reset_password'); ?></h2>
                    <p class="subtitle"><?php echo $lang === 'ar' ? 'أدخل كلمة المرور الجديدة' : 'Enter your new password'; ?></p>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                        <div><?php echo sanitizeOutput($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('new_password'); ?></label>
                            <input type="password" name="password" class="form-input" minlength="8" required>
                            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;"><?php echo __('password_min_length_hint'); ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo __('confirm_password'); ?></label>
                            <input type="password" name="confirm_password" class="form-input" minlength="8" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <?php echo __('reset_password'); ?>
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="auth-footer">
                    <p><?php echo __('remember_password'); ?> <a href="<?php echo BASE_URL; ?>/pages/login.php"><?php echo __('login'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
