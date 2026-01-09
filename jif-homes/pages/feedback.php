<?php
/**
 * JIF HOMES - Feedback Form Page
 */

$pageTitle = 'feedback';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان. يرجى المحاولة مرة أخرى.' : 'Security error. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $feedbackType = sanitizeInput($_POST['feedback_type'] ?? 'general');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        if (empty($name)) {
            $errors[] = __('required_field') . ' - ' . __('your_name');
        }
        
        if (empty($email)) {
            $errors[] = __('required_field') . ' - ' . __('email');
        } elseif (!validateEmail($email)) {
            $errors[] = __('invalid_email');
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = __('invalid_phone');
        }
        
        if (empty($message)) {
            $errors[] = __('required_field') . ' - ' . __('message');
        }
        
        // Check if email already exists
        if (empty($errors)) {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT id FROM feedback WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $errors[] = __('feedback_email_exists');
                }
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'خطأ في قاعدة البيانات' : 'Database error';
            }
        }
        
        // Insert feedback if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO feedback (name, email, phone, feedback_type, rating, message, status, created_at) 
                    VALUES (?, ?, ?, ?, 5, ?, 'new', NOW())
                ");
                $stmt->execute([$name, $email, $phone, $feedbackType, $message]);
                
                $success = true;
                setFlashMessage('success', __('feedback_success'));
                
                $name = $email = $phone = $message = '';
                $feedbackType = 'general';
                
            } catch (PDOException $e) {
                $errors[] = __('feedback_error');
            }
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('feedback_form'); ?></h2>
            <div class="section-divider"></div>
            <p><?php echo __('feedback_desc'); ?></p>
        </div>
        
        <div style="max-width: 700px; margin: 0 auto;">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-inline-end: 0.5rem;"><polyline points="20,6 9,17 4,12"/></svg>
                <?php echo __('feedback_success'); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                <div><?php echo sanitizeOutput($error); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST" class="form-container" id="feedbackForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <?php echo __('your_name'); ?> <span style="color: var(--error);">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-input" 
                               value="<?php echo sanitizeOutput($name ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <?php echo __('email'); ?> <span style="color: var(--error);">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo sanitizeOutput($email ?? ''); ?>"
                               required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            <?php echo __('your_phone'); ?>
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-input" 
                               value="<?php echo sanitizeOutput($phone ?? ''); ?>"
                               dir="ltr">
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_type" class="form-label">
                            <?php echo __('feedback_type'); ?> <span style="color: var(--error);">*</span>
                        </label>
                        <select id="feedback_type" name="feedback_type" class="form-select" required>
                            <option value="general" <?php echo ($feedbackType ?? '') === 'general' ? 'selected' : ''; ?>><?php echo __('general'); ?></option>
                            <option value="complaint" <?php echo ($feedbackType ?? '') === 'complaint' ? 'selected' : ''; ?>><?php echo __('complaint'); ?></option>
                            <option value="suggestion" <?php echo ($feedbackType ?? '') === 'suggestion' ? 'selected' : ''; ?>><?php echo __('suggestion'); ?></option>
                            <option value="inquiry" <?php echo ($feedbackType ?? '') === 'inquiry' ? 'selected' : ''; ?>><?php echo __('inquiry'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">
                        <?php echo __('your_feedback'); ?> <span style="color: var(--error);">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              class="form-textarea" 
                              rows="6"
                              required><?php echo sanitizeOutput($message ?? ''); ?></textarea>
                </div>
                
                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?php echo __('submit_feedback'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
@media (max-width: 600px) {
    .form-container div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
