<?php
/**
 * JIF HOMES - Forgot Password Page
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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $errors[] = __('required_field');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('invalid_email');
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT id, first_name FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                    $stmt->execute([$token, $expires, $user['id']]);
                }
                // Always show success (security - don't reveal if email exists)
                $success = true;
            } catch (PDOException $e) {
                $success = true;
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
    <title><?php echo __('forgot_password'); ?> - <?php echo __('site_name'); ?></title>
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
                <p style="margin-top: 1rem; opacity: 0.9;"><?php echo $lang === 'ar' ? 'استعادة الوصول إلى حسابك' : 'Recover access to your account'; ?></p>
            </div>
            
            <div class="auth-form">
                <div class="lang-switcher" style="margin-bottom: 2rem;">
                    <a href="?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">العربية</a>
                    <span class="divider">|</span>
                    <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                
                <?php if ($success): ?>
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; background: #E3F2FD; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1565C0" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <h2><?php echo $lang === 'ar' ? 'تم الإرسال!' : 'Email Sent!'; ?></h2>
                        <p class="subtitle"><?php echo __('reset_email_sent'); ?></p>
                        <a href="<?php echo BASE_URL; ?>/pages/login.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1.5rem;">
                            <?php echo __('back_to_login'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <h2><?php echo __('forgot_password'); ?></h2>
                    <p class="subtitle"><?php echo __('forgot_password_desc'); ?></p>
                    
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
                            <label class="form-label"><?php echo __('email'); ?></label>
                            <input type="email" name="email" class="form-input" value="<?php echo sanitizeOutput($email); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <?php echo __('send_reset_link'); ?>
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
