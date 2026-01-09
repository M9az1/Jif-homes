<?php
/**
 * JIF HOMES - Registration Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/translations.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$dir = getDirection();

$errors = [];
$firstName = $lastName = $email = $phone = '';

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $agreeTerms = isset($_POST['agree_terms']);
        
        // Validation
        if (empty($firstName)) $errors[] = __('required_field') . ' - ' . __('first_name');
        if (empty($lastName)) $errors[] = __('required_field') . ' - ' . __('last_name');
        if (empty($email)) $errors[] = __('required_field') . ' - ' . __('email');
        elseif (!validateEmail($email)) $errors[] = __('invalid_email');
        if (!empty($phone) && !validatePhone($phone)) $errors[] = __('invalid_phone');
        if (empty($password)) $errors[] = __('required_field') . ' - ' . __('password');
        elseif (!validatePassword($password)) $errors[] = __('password_requirements');
        if ($password !== $confirmPassword) $errors[] = __('passwords_not_match');
        if (!$agreeTerms) $errors[] = __('must_agree_terms');
        
        // Check if email exists
        if (empty($errors)) {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = __('email_exists');
                }
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'خطأ في قاعدة البيانات' : 'Database error';
            }
        }
        
        // Create account
        if (empty($errors)) {
            try {
                $hashedPassword = hashPassword($password);
                $stmt = $db->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'user', 1, NOW())
                ");
                $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
                
                // Auto login
                $userId = $db->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                $_SESSION['user_role'] = 'user';
                
                setFlashMessage('success', __('register_success'));
                header('Location: ' . BASE_URL . '/pages/index.php');
                exit;
                
            } catch (PDOException $e) {
                $errors[] = __('register_error');
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
    <title><?php echo __('register'); ?> - <?php echo __('site_name'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-image">
                <div class="logo-img" style="width: 80px; height: 80px; font-size: 2rem; margin-bottom: var(--space-lg);">JH</div>
                <h2><?php echo __('site_name'); ?></h2>
                <p><?php echo __('site_tagline'); ?></p>
                <div style="margin-top: var(--space-2xl);">
                    <p><?php echo $lang === 'ar' ? 'انضم إلينا واستمتع بأفضل تجربة إيجار' : 'Join us and enjoy the best rental experience'; ?></p>
                </div>
            </div>
            
            <div class="auth-form">
                <div class="lang-switch" style="margin-bottom: var(--space-xl);">
                    <a href="?lang=ar" class="lang-btn <?php echo $lang === 'ar' ? 'active' : ''; ?>">العربية</a>
                    <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>">English</a>
                </div>
                
                <h2><?php echo __('create_account'); ?></h2>
                <p class="subtitle"><?php echo __('register_subtitle'); ?></p>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-inline-start: 20px;">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitizeOutput($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="registerForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label"><?php echo __('first_name'); ?> <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo sanitizeOutput($firstName); ?>" data-validate="required" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label"><?php echo __('last_name'); ?> <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo sanitizeOutput($lastName); ?>" data-validate="required" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label"><?php echo __('email'); ?> <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo sanitizeOutput($email); ?>" data-validate="required|email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label"><?php echo __('phone'); ?></label>
                        <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo sanitizeOutput($phone); ?>" data-validate="phone" dir="ltr">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label"><?php echo __('password'); ?> <span class="required">*</span></label>
                            <input type="password" id="password" name="password" class="form-input" data-validate="required|password" required>
                            <span class="form-hint"><?php echo __('password_requirements'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label"><?php echo __('confirm_password'); ?> <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" data-validate="required|match:password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="agree_terms" required>
                            <span><?php echo __('agree_terms'); ?> <span class="required">*</span></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <?php echo __('register'); ?>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p><?php echo __('have_account'); ?> <a href="<?php echo BASE_URL; ?>/pages/login.php"><?php echo __('login'); ?></a></p>
                </div>
                
                <div style="text-align: center; margin-top: var(--space-lg);">
                    <a href="<?php echo BASE_URL; ?>/pages/index.php" style="font-size: 0.875rem;">← <?php echo __('home'); ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/script/validation.js"></script>
</body>
</html>
