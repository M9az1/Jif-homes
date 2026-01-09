/**
 * JIF HOMES - Main JavaScript
 * Core functionality and utilities
 */

(function() {
    'use strict';

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Current Language
    const currentLang = document.documentElement.lang || 'ar';
    const isRTL = document.documentElement.dir === 'rtl';

    // API Helper
    const API = {
        post: async function(url, data) {
            try {
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: 'Network error' };
            }
        },
        
        get: async function(url) {
            try {
                const response = await fetch(url);
                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                return { success: false, error: 'Network error' };
            }
        }
    };

    // Toast Notifications
    const Toast = {
        container: null,
        
        init: function() {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                ${isRTL ? 'left' : 'right'}: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(this.container);
        },
        
        show: function(message, type = 'info', duration = 5000) {
            if (!this.container) this.init();
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                padding: 16px 24px;
                border-radius: 8px;
                color: white;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                animation: slideIn 0.3s ease;
                background: ${type === 'success' ? '#059669' : type === 'error' ? '#DC2626' : type === 'warning' ? '#D97706' : '#2563EB'};
            `;
            
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            toast.innerHTML = `<span>${icons[type]}</span><span>${message}</span>`;
            this.container.appendChild(toast);
            
            // Auto remove
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        },
        
        success: function(message) { this.show(message, 'success'); },
        error: function(message) { this.show(message, 'error'); },
        warning: function(message) { this.show(message, 'warning'); },
        info: function(message) { this.show(message, 'info'); }
    };

    // Add toast animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(${isRTL ? '-100%' : '100%'}); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(${isRTL ? '-100%' : '100%'}); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Modal Handler
    const Modal = {
        open: function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        },
        
        close: function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        },
        
        init: function() {
            // Close on backdrop click
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.addEventListener('click', () => {
                    backdrop.closest('.modal').classList.remove('show');
                    document.body.style.overflow = '';
                });
            });
            
            // Close on close button click
            document.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.closest('.modal').classList.remove('show');
                    document.body.style.overflow = '';
                });
            });
            
            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal.show').forEach(modal => {
                        modal.classList.remove('show');
                    });
                    document.body.style.overflow = '';
                }
            });
        }
    };

    // Gallery/Lightbox
    const Gallery = {
        currentIndex: 0,
        images: [],
        
        init: function() {
            document.querySelectorAll('[data-gallery]').forEach(gallery => {
                const images = gallery.querySelectorAll('[data-gallery-item]');
                images.forEach((img, index) => {
                    img.addEventListener('click', () => this.open(gallery, index));
                });
            });
        },
        
        open: function(gallery, index) {
            this.images = Array.from(gallery.querySelectorAll('[data-gallery-item]')).map(img => ({
                src: img.dataset.src || img.src,
                alt: img.alt
            }));
            this.currentIndex = index;
            this.render();
        },
        
        render: function() {
            // Create lightbox if not exists
            let lightbox = document.getElementById('lightbox');
            if (!lightbox) {
                lightbox = document.createElement('div');
                lightbox.id = 'lightbox';
                lightbox.innerHTML = `
                    <div class="lightbox-backdrop"></div>
                    <div class="lightbox-content">
                        <button class="lightbox-close">&times;</button>
                        <button class="lightbox-prev">${isRTL ? '→' : '←'}</button>
                        <img src="" alt="" class="lightbox-image">
                        <button class="lightbox-next">${isRTL ? '←' : '→'}</button>
                    </div>
                `;
                lightbox.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 10000;
                    display: none;
                `;
                document.body.appendChild(lightbox);
                
                // Add styles
                const style = document.createElement('style');
                style.textContent = `
                    .lightbox-backdrop {
                        position: absolute;
                        top: 0; left: 0; right: 0; bottom: 0;
                        background: rgba(0,0,0,0.9);
                    }
                    .lightbox-content {
                        position: relative;
                        width: 100%;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .lightbox-image {
                        max-width: 90%;
                        max-height: 90%;
                        object-fit: contain;
                    }
                    .lightbox-close, .lightbox-prev, .lightbox-next {
                        position: absolute;
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        font-size: 24px;
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        cursor: pointer;
                        transition: background 0.3s;
                    }
                    .lightbox-close:hover, .lightbox-prev:hover, .lightbox-next:hover {
                        background: rgba(255,255,255,0.3);
                    }
                    .lightbox-close { top: 20px; right: 20px; }
                    .lightbox-prev { left: 20px; }
                    .lightbox-next { right: 20px; }
                `;
                document.head.appendChild(style);
                
                // Event listeners
                lightbox.querySelector('.lightbox-backdrop').addEventListener('click', () => this.close());
                lightbox.querySelector('.lightbox-close').addEventListener('click', () => this.close());
                lightbox.querySelector('.lightbox-prev').addEventListener('click', () => this.prev());
                lightbox.querySelector('.lightbox-next').addEventListener('click', () => this.next());
            }
            
            lightbox.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            const img = lightbox.querySelector('.lightbox-image');
            img.src = this.images[this.currentIndex].src;
            img.alt = this.images[this.currentIndex].alt;
        },
        
        close: function() {
            const lightbox = document.getElementById('lightbox');
            if (lightbox) {
                lightbox.style.display = 'none';
                document.body.style.overflow = '';
            }
        },
        
        next: function() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
            this.render();
        },
        
        prev: function() {
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            this.render();
        }
    };

    // Map Handler
    const MapHandler = {
        map: null,
        markers: [],
        
        init: function(elementId, options = {}) {
            const element = document.getElementById(elementId);
            if (!element) return null;
            
            const defaultOptions = {
                center: [24.7136, 46.6753], // Riyadh
                zoom: 10
            };
            
            const settings = { ...defaultOptions, ...options };
            
            this.map = L.map(elementId).setView(settings.center, settings.zoom);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);
            
            return this.map;
        },
        
        addMarker: function(lat, lng, options = {}) {
            if (!this.map) return null;
            
            const marker = L.marker([lat, lng]).addTo(this.map);
            
            if (options.popup) {
                marker.bindPopup(options.popup);
            }
            
            if (options.onClick) {
                marker.on('click', options.onClick);
            }
            
            this.markers.push(marker);
            return marker;
        },
        
        clearMarkers: function() {
            this.markers.forEach(marker => this.map.removeLayer(marker));
            this.markers = [];
        },
        
        fitBounds: function() {
            if (this.markers.length > 0) {
                const group = L.featureGroup(this.markers);
                this.map.fitBounds(group.getBounds().pad(0.1));
            }
        },
        
        setView: function(lat, lng, zoom = 15) {
            if (this.map) {
                this.map.setView([lat, lng], zoom);
            }
        }
    };

    // Favorites Handler
    const Favorites = {
        toggle: async function(apartmentId, button) {
            const result = await API.post('/pages/api/favorites.php', {
                action: 'toggle',
                apartment_id: apartmentId
            });
            
            if (result.success) {
                button.classList.toggle('active');
                Toast.success(result.message);
            } else {
                if (result.error === 'login_required') {
                    Toast.warning(currentLang === 'ar' ? 'يرجى تسجيل الدخول أولاً' : 'Please login first');
                    window.location.href = '/pages/login.php';
                } else {
                    Toast.error(result.error);
                }
            }
        }
    };

    // Search Handler
    const Search = {
        init: function() {
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    // Allow form submission, just validate
                    const location = this.querySelector('[name="location"]');
                    const checkIn = this.querySelector('[name="check_in"]');
                    const checkOut = this.querySelector('[name="check_out"]');
                    
                    if (checkIn && checkOut && checkIn.value && checkOut.value) {
                        if (new Date(checkIn.value) >= new Date(checkOut.value)) {
                            e.preventDefault();
                            Toast.error(currentLang === 'ar' ? 'تاريخ المغادرة يجب أن يكون بعد تاريخ الوصول' : 'Check-out must be after check-in');
                        }
                    }
                });
            }
        }
    };

    // Image Preview Handler (for file inputs)
    const ImagePreview = {
        init: function() {
            document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
                input.addEventListener('change', function() {
                    const previewContainer = document.querySelector(this.dataset.preview);
                    if (!previewContainer) return;
                    
                    previewContainer.innerHTML = '';
                    
                    Array.from(this.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.cssText = 'width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin: 4px;';
                                previewContainer.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });
            });
        }
    };

    // Smooth Scroll
    const SmoothScroll = {
        init: function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        }
    };

    // Counter Animation
    const CounterAnimation = {
        init: function() {
            const counters = document.querySelectorAll('[data-counter]');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.animate(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            counters.forEach(counter => observer.observe(counter));
        },
        
        animate: function(element) {
            const target = parseInt(element.dataset.counter);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }
    };

    // Initialize everything on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        Modal.init();
        Gallery.init();
        Search.init();
        ImagePreview.init();
        SmoothScroll.init();
        CounterAnimation.init();
    });

    // Expose globally
    window.JifApp = {
        API: API,
        Toast: Toast,
        Modal: Modal,
        Gallery: Gallery,
        MapHandler: MapHandler,
        Favorites: Favorites,
        Search: Search
    };

})();
