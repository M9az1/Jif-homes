<?php
/**
 * JIF HOMES - Admin Header Include
 * Brand Guidelines Ver 1.1 - Oct 2025
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/translations.php';

// Require admin access
requireAdmin();

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
}

$lang = getCurrentLanguage();
$dir = getDirection();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo isset($pageTitle) ? __($pageTitle) . ' - ' : ''; ?><?php echo __('admin_dashboard'); ?> - <?php echo $lang === 'ar' ? 'جيف هومز' : 'JIF HOMES'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="logo-container">
                <img src="<?php echo BASE_URL; ?>/image/logo-white.png" alt="JIF HOMES" style="height: 40px; margin-bottom: var(--space-sm);">
                <div class="logo-subtext"><?php echo __('admin_panel'); ?></div>
            </div>
            
            <nav>
                <div class="admin-nav-section">
                    <div class="admin-nav-title"><?php echo $lang === 'ar' ? 'الرئيسية' : 'Main'; ?></div>
                    <ul class="admin-nav">
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                <span><?php echo __('dashboard'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title"><?php echo $lang === 'ar' ? 'الإدارة' : 'Management'; ?></div>
                    <ul class="admin-nav">
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/apartments.php" <?php echo basename($_SERVER['PHP_SELF']) === 'apartments.php' || basename($_SERVER['PHP_SELF']) === 'apartment-form.php' ? 'class="active"' : ''; ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
                                <span><?php echo __('manage_apartments'); ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/locations.php" <?php echo basename($_SERVER['PHP_SELF']) === 'locations.php' ? 'class="active"' : ''; ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span><?php echo __('manage_locations'); ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/users.php" <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'class="active"' : ''; ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                <span><?php echo __('manage_users'); ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/admin/feedback.php" <?php echo basename($_SERVER['PHP_SELF']) === 'feedback.php' ? 'class="active"' : ''; ?>>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z"/></svg>
                                <span><?php echo __('manage_feedback'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-nav-section">
                    <div class="admin-nav-title"><?php echo $lang === 'ar' ? 'النظام' : 'System'; ?></div>
                    <ul class="admin-nav">
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/index.php">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                                <span><?php echo $lang === 'ar' ? 'عرض الموقع' : 'View Site'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/pages/logout.php">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span><?php echo __('logout'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="admin-user-profile">
                <div class="admin-user-info">
                    <div class="admin-user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="admin-user-details">
                        <div class="admin-user-name"><?php echo sanitizeOutput($_SESSION['user_name'] ?? 'Admin'); ?></div>
                        <div class="admin-user-role"><?php echo $lang === 'ar' ? 'مدير' : 'Administrator'; ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <div class="admin-topbar-left">
                    <h1><?php echo isset($pageTitle) ? __($pageTitle) : __('dashboard'); ?></h1>
                    <div class="breadcrumb">
                        <a href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php"><?php echo __('admin_panel'); ?></a>
                        <span>/</span>
                        <span><?php echo isset($pageTitle) ? __($pageTitle) : __('dashboard'); ?></span>
                    </div>
                </div>
                <div class="admin-topbar-right">
                    <div class="lang-switch">
                        <a href="?lang=ar" class="lang-btn <?php echo $lang === 'ar' ? 'active' : ''; ?>" style="color: var(--text-primary);">العربية</a>
                        <a href="?lang=en" class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" style="color: var(--text-primary);">English</a>
                    </div>
                </div>
            </div>
            
            <?php 
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo sanitizeOutput($flash['type']); ?>">
                <?php echo sanitizeOutput($flash['message']); ?>
            </div>
            <?php endif; ?>
