<?php
/**
 * JIF HOMES - Login Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translations.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$dir = getDirection();

$errors = [];
$email = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $errors[] = __('required_field');
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && verifyPassword($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Redirect
                    $redirect = $_SESSION['redirect_url'] ?? BASE_URL . '/pages/index.php';
                    unset($_SESSION['redirect_url']);
                    
                    if ($user['role'] === 'admin') {
                        $redirect = BASE_URL . '/pages/admin/dashboard.php';
                    }
                    
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $errors[] = __('login_error');
                }
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
    <title><?php echo __('login'); ?> - <?php echo __('site_name'); ?></title>
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
                <p style="margin-top: 1rem; opacity: 0.9;"><?php echo $lang === 'ar' ? 'مرحبا بك في منصتنا للإيجار اليومي' : 'Welcome to our daily rental platform'; ?></p>
            </div>
            
            <div class="auth-form">
                <div class="lang-switch" style="margin-bottom: var(--space-xl);">
                    <a href="?lang=ar" class="lang-btn <?php echo $lang === 'ar' ? 'active' : ''; ?>">العربية</a>
                    <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">English</a>
                </div>
                
                <h2><?php echo __('welcome_back'); ?></h2>
                <p class="subtitle"><?php echo __('login_subtitle'); ?></p>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                    <div><?php echo sanitizeOutput($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="loginForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="email" class="form-label"><?php echo __('email'); ?></label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo sanitizeOutput($email); ?>"
                               data-validate="required|email"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label"><?php echo __('password'); ?></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input"
                               data-validate="required"
                               required>
                    </div>
                    
                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-check">
                            <input type="checkbox" name="remember">
                            <span><?php echo __('remember_me'); ?></span>
                        </label>
                        <a href="<?php echo BASE_URL; ?>/pages/forgot-password.php" style="font-size: 0.875rem;"><?php echo __('forgot_password'); ?></a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <?php echo __('login'); ?>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p><?php echo __('no_account'); ?> <a href="<?php echo BASE_URL; ?>/pages/register.php"><?php echo __('create_account'); ?></a></p>
                </div>
                
                <div style="text-align: center; margin-top: var(--space-lg);">
                    <a href="<?php echo BASE_URL; ?>/pages/index.php" style="font-size: 0.875rem;"><?php echo $lang === 'ar' ? '← الرئيسية' : '← Home'; ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script/validation.js"></script>
</body>
</html>
