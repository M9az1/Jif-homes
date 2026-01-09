        </main>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?php echo BASE_URL; ?>/script/main.js"></script>
    <script src="<?php echo BASE_URL; ?>/script/validation.js"></script>
    
    <script>
        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 300);
            }, 5000);
        });
        
        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || '<?php echo __("confirm_delete"); ?>');
        }
    </script>
</body>
</html>
