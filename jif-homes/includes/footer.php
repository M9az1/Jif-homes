    </main>
    
    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-container">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <img src="<?php echo BASE_URL; ?>/image/logo-white.png" alt="JIF HOMES" class="footer-logo">
                    <p class="footer-desc">
                        <?php echo $lang === 'ar' 
                            ? 'بدأت Jif Homes من شغف حقيقي بالضيافة وإدارة الأملاك. نقدم خدمات إيجار يومية وشهرية بجودة عالية مستوحاة من القيم السعودية.' 
                            : 'Jif Homes was born from a genuine passion for hospitality and property management. We offer quality daily and monthly rentals inspired by Saudi values.'; ?>
                    </p>
                    <div class="footer-social">
                        <a href="https://wa.me/966548224343" target="_blank" aria-label="WhatsApp">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/jif.homes/" target="_blank" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://www.tiktok.com/@jif.homes/" target="_blank" aria-label="TikTok">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-links">
                    <h4><?php echo $lang === 'ar' ? 'روابط سريعة' : 'Quick Links'; ?></h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/pages/index.php"><?php echo __('home'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/apartments.php"><?php echo __('apartments'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/feedback.php"><?php echo __('feedback'); ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/contact.php"><?php echo __('contact'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="footer-links">
                    <h4><?php echo $lang === 'ar' ? 'خدماتنا' : 'Our Services'; ?></h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/pages/apartments.php"><?php echo $lang === 'ar' ? 'إيجار يومي' : 'Daily Rental'; ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/apartments.php"><?php echo $lang === 'ar' ? 'إيجار شهري' : 'Monthly Rental'; ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/apartments.php"><?php echo $lang === 'ar' ? 'شقق VIP' : 'VIP Apartments'; ?></a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-contact">
                    <h4><?php echo __('contact_us'); ?></h4>
                    <ul>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><?php echo $lang === 'ar' ? 'المملكة العربية السعودية' : 'Saudi Arabia'; ?></span>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                            <a href="https://wa.me/966548224343" dir="ltr">+966 54 822 4343</a>
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <a href="mailto:info@jifhomes.sa">info@jifhomes.sa</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-container">
                <p><?php echo date('Y'); ?> JIF HOMES. <?php echo $lang === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved'; ?>.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?php echo BASE_URL; ?>/script/main.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobileToggle')?.addEventListener('click', function() {
            this.classList.toggle('active');
            document.getElementById('mainNav')?.classList.toggle('active');
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
