<?php
/**
 * JIF HOMES - Logout
 */

require_once __DIR__ . '/../includes/config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to home
header('Location: ' . BASE_URL . '/pages/index.php');
exit;
