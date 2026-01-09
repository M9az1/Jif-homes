<?php
/**
 * JIF HOMES - Header Include
 * Brand Guidelines Ver 1.1 - Oct 2025
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/translations.php';

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$dir = getDirection();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo isset($pageTitle) ? __($pageTitle) . ' - ' : ''; ?><?php echo __('site_name'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <a href="#main-content" class="skip-link"><?php echo $lang === 'ar' ? 'تخطي إلى المحتوى' : 'Skip to content'; ?></a>
    
    <header class="site-header">
        <div class="header-container">
            <!-- Logo - Always first (right in RTL, left in LTR) -->
            <a href="<?php echo BASE_URL; ?>/pages/index.php" class="header-logo">
                <img src="<?php echo BASE_URL; ?>/image/logo.png" alt="JIF HOMES">
            </a>
            
            <!-- Main Navigation -->
            <nav class="header-nav" id="mainNav">
                <?php if ($lang === 'ar'): ?>
                <!-- Arabic: Right to left order - الرئيسية ← الشقق ← آراء العملاء ← اتصل بنا -->
                <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="nav-item <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
                    <?php echo __('contact'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/feedback.php" class="nav-item <?php echo $currentPage === 'feedback' ? 'active' : ''; ?>">
                    <?php echo __('feedback'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/apartments.php" class="nav-item <?php echo $currentPage === 'apartments' || $currentPage === 'apartment' ? 'active' : ''; ?>">
                    <?php echo __('apartments'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <?php echo __('home'); ?>
                </a>
                <?php else: ?>
                <!-- English: Left to right order - Home → Apartments → Feedback → Contact -->
                <a href="<?php echo BASE_URL; ?>/pages/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <?php echo __('home'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/apartments.php" class="nav-item <?php echo $currentPage === 'apartments' || $currentPage === 'apartment' ? 'active' : ''; ?>">
                    <?php echo __('apartments'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/feedback.php" class="nav-item <?php echo $currentPage === 'feedback' ? 'active' : ''; ?>">
                    <?php echo __('feedback'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="nav-item <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">
                    <?php echo __('contact'); ?>
                </a>
                <?php endif; ?>
            </nav>
            
            <!-- Actions: Language + Auth - Always last (left in RTL, right in LTR) -->
            <div class="header-actions">
                <!-- Language Switch -->
                <div class="lang-switcher">
                    <a href="?lang=ar" class="<?php echo $lang === 'ar' ? 'active' : ''; ?>">العربية</a>
                    <span class="divider">|</span>
                    <a href="?lang=en" class="<?php echo $lang === 'en' ? 'active' : ''; ?>">EN</a>
                </div>
                
                <!-- Auth Buttons -->
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/account.php" class="header-btn outline">
                        <?php echo __('my_account'); ?>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php" class="header-btn primary">
                            <?php echo __('admin_panel'); ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/pages/login.php" class="header-btn outline">
                        <?php echo __('login'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/register.php" class="header-btn primary">
                        <?php echo __('register'); ?>
                    </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-toggle" id="mobileToggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <main id="main-content">
    
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
    <div class="container" style="padding-top: 1rem;">
        <div class="alert alert-<?php echo sanitizeOutput($flash['type']); ?>">
            <?php echo sanitizeOutput($flash['message']); ?>
        </div>
    </div>
    <?php endif; ?>
