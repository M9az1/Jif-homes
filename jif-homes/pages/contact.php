<?php
/**
 * JIF HOMES - Contact Us Page
 */

$pageTitle = 'contact_us';
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = $lang === 'ar' ? 'خطأ في الأمان' : 'Security error';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        
        // Validation
        if (empty($name)) $errors[] = __('required_field') . ' - ' . __('your_name');
        if (empty($email)) $errors[] = __('required_field') . ' - ' . __('email');
        elseif (!validateEmail($email)) $errors[] = __('invalid_email');
        if (empty($message)) $errors[] = __('required_field') . ' - ' . __('message');
        
        if (empty($errors)) {
            try {
                $db = getDB();
                $stmt = $db->prepare("
                    INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $email, $phone, $subject, $message]);
                $success = true;
                $name = $email = $phone = $subject = $message = '';
            } catch (PDOException $e) {
                $errors[] = $lang === 'ar' ? 'حدث خطأ' : 'An error occurred';
            }
        }
    }
}
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo __('contact_us'); ?></h2>
            <div class="section-divider"></div>
            <p><?php echo __('contact_us_desc'); ?></p>
        </div>
        
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info-cards">
                <div class="contact-card">
                    <div class="contact-card-icon">📍</div>
                    <div>
                        <h4><?php echo __('our_location'); ?></h4>
                        <p><?php echo $lang === 'ar' ? 'الرياض، حي العليا، برج المملكة' : 'Riyadh, Olaya District, Kingdom Tower'; ?></p>
                        <p><?php echo $lang === 'ar' ? 'المملكة العربية السعودية' : 'Saudi Arabia'; ?></p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-card-icon">📞</div>
                    <div>
                        <h4><?php echo __('phone_number'); ?></h4>
                        <p dir="ltr">+966 50 000 0000</p>
                        <p dir="ltr">+966 11 000 0000</p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-card-icon">✉️</div>
                    <div>
                        <h4><?php echo __('email_address'); ?></h4>
                        <p>info@jifhomes.com</p>
                        <p>support@jifhomes.com</p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-card-icon">🕐</div>
                    <div>
                        <h4><?php echo __('working_hours'); ?></h4>
                        <p><?php echo __('working_hours_value'); ?></p>
                        <p><?php echo $lang === 'ar' ? 'الجمعة: مغلق' : 'Friday: Closed'; ?></p>
                    </div>
                </div>
                
                <!-- Map -->
                <div class="map-container" style="height: 250px; margin-top: var(--space-lg);">
                    <div id="contactMap" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="form-container">
                <h3 class="form-section-title"><?php echo __('send_message'); ?></h3>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    ✓ <?php echo $lang === 'ar' ? 'تم إرسال رسالتك بنجاح!' : 'Your message has been sent successfully!'; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-inline-start: 20px;">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitizeOutput($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" id="contactForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="name" class="form-label"><?php echo __('your_name'); ?> <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-input" value="<?php echo sanitizeOutput($name ?? ''); ?>" data-validate="required" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label"><?php echo __('email'); ?> <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input" value="<?php echo sanitizeOutput($email ?? ''); ?>" data-validate="required|email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="form-label"><?php echo __('your_phone'); ?></label>
                            <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo sanitizeOutput($phone ?? ''); ?>" data-validate="phone" dir="ltr">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label"><?php echo __('subject'); ?></label>
                        <input type="text" id="subject" name="subject" class="form-input" value="<?php echo sanitizeOutput($subject ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label"><?php echo __('message'); ?> <span class="required">*</span></label>
                        <textarea id="message" name="message" class="form-textarea" rows="5" data-validate="required" required><?php echo sanitizeOutput($message ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;"><?php echo __('send'); ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = JifApp.MapHandler.init('contactMap', {
        center: [24.6908, 46.6853],
        zoom: 15
    });
    
    if (map) {
        JifApp.MapHandler.addMarker(24.6908, 46.6853, {
            popup: '<strong><?php echo __('site_name'); ?></strong><br><?php echo $lang === "ar" ? "المكتب الرئيسي" : "Main Office"; ?>'
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
