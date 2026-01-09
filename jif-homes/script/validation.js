/**
 * JIF HOMES - Form Validation
 * Client-side validation for all forms
 */

(function() {
    'use strict';

    // Validation Rules
    const ValidationRules = {
        required: function(value) {
            return value !== null && value !== undefined && value.toString().trim() !== '';
        },
        
        email: function(value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(value);
        },
        
        phone: function(value) {
            // Remove all non-numeric except +
            const cleaned = value.replace(/[^0-9+]/g, '');
            return cleaned.length >= 9 && cleaned.length <= 15;
        },
        
        minLength: function(value, min) {
            return value.length >= min;
        },
        
        maxLength: function(value, max) {
            return value.length <= max;
        },
        
        password: function(value) {
            // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
            return value.length >= 8 && 
                   /[A-Z]/.test(value) && 
                   /[a-z]/.test(value) && 
                   /[0-9]/.test(value);
        },
        
        passwordMatch: function(value, compareValue) {
            return value === compareValue;
        },
        
        checked: function(element) {
            return element.checked;
        }
    };

    // Get current language for error messages
    function getCurrentLang() {
        return document.documentElement.lang || 'ar';
    }

    // Error messages in both languages
    const ErrorMessages = {
        ar: {
            required: 'هذا الحقل مطلوب',
            email: 'البريد الإلكتروني غير صالح',
            phone: 'رقم الهاتف غير صالح',
            password: 'كلمة المرور يجب أن تكون 8 أحرف على الأقل وتحتوي على حرف كبير وحرف صغير ورقم',
            passwordMatch: 'كلمات المرور غير متطابقة',
            minLength: 'يجب أن يكون على الأقل {min} أحرف',
            maxLength: 'يجب ألا يتجاوز {max} أحرف',
            checked: 'يجب الموافقة على هذا الخيار',
            rating: 'الرجاء اختيار تقييم'
        },
        en: {
            required: 'This field is required',
            email: 'Please enter a valid email address',
            phone: 'Please enter a valid phone number',
            password: 'Password must be at least 8 characters with uppercase, lowercase, and number',
            passwordMatch: 'Passwords do not match',
            minLength: 'Must be at least {min} characters',
            maxLength: 'Must not exceed {max} characters',
            checked: 'This option must be checked',
            rating: 'Please select a rating'
        }
    };

    // Get error message
    function getErrorMessage(rule, params = {}) {
        const lang = getCurrentLang();
        let message = ErrorMessages[lang][rule] || ErrorMessages['en'][rule] || 'Invalid value';
        
        // Replace placeholders
        Object.keys(params).forEach(key => {
            message = message.replace(`{${key}}`, params[key]);
        });
        
        return message;
    }

    // Show error for a field
    function showError(field, message) {
        // Remove existing error
        clearError(field);
        
        // Add error class
        field.classList.add('error');
        
        // Create error element
        const errorElement = document.createElement('span');
        errorElement.className = 'form-error show';
        errorElement.textContent = message;
        
        // Insert after field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    // Clear error for a field
    function clearError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Validate a single field
    function validateField(field) {
        const rules = field.dataset.validate ? field.dataset.validate.split('|') : [];
        const value = field.type === 'checkbox' ? field.checked : field.value.trim();
        
        // Check required first
        if (field.hasAttribute('required') || rules.includes('required')) {
            if (field.type === 'checkbox') {
                if (!ValidationRules.checked(field)) {
                    showError(field, getErrorMessage('checked'));
                    return false;
                }
            } else if (!ValidationRules.required(value)) {
                showError(field, getErrorMessage('required'));
                return false;
            }
        }
        
        // Skip other validations if empty and not required
        if (!value && !field.hasAttribute('required')) {
            clearError(field);
            return true;
        }
        
        // Check other rules
        for (const rule of rules) {
            if (rule === 'required') continue;
            
            // Check for rules with parameters
            const [ruleName, param] = rule.split(':');
            
            switch (ruleName) {
                case 'email':
                    if (!ValidationRules.email(value)) {
                        showError(field, getErrorMessage('email'));
                        return false;
                    }
                    break;
                    
                case 'phone':
                    if (!ValidationRules.phone(value)) {
                        showError(field, getErrorMessage('phone'));
                        return false;
                    }
                    break;
                    
                case 'password':
                    if (!ValidationRules.password(value)) {
                        showError(field, getErrorMessage('password'));
                        return false;
                    }
                    break;
                    
                case 'minLength':
                    if (!ValidationRules.minLength(value, parseInt(param))) {
                        showError(field, getErrorMessage('minLength', { min: param }));
                        return false;
                    }
                    break;
                    
                case 'maxLength':
                    if (!ValidationRules.maxLength(value, parseInt(param))) {
                        showError(field, getErrorMessage('maxLength', { max: param }));
                        return false;
                    }
                    break;
                    
                case 'match':
                    const matchField = document.getElementById(param);
                    if (matchField && !ValidationRules.passwordMatch(value, matchField.value)) {
                        showError(field, getErrorMessage('passwordMatch'));
                        return false;
                    }
                    break;
            }
        }
        
        clearError(field);
        return true;
    }

    // Validate rating (for feedback form)
    function validateRating(form) {
        const ratingInputs = form.querySelectorAll('input[name="rating"]');
        if (ratingInputs.length === 0) return true;
        
        let hasRating = false;
        ratingInputs.forEach(input => {
            if (input.checked) hasRating = true;
        });
        
        const ratingGroup = form.querySelector('.rating-group');
        if (ratingGroup) {
            if (!hasRating) {
                // Show error for rating
                const existingError = ratingGroup.parentNode.querySelector('.form-error');
                if (!existingError) {
                    const errorElement = document.createElement('span');
                    errorElement.className = 'form-error show';
                    errorElement.textContent = getErrorMessage('rating');
                    ratingGroup.parentNode.appendChild(errorElement);
                }
                return false;
            } else {
                const existingError = ratingGroup.parentNode.querySelector('.form-error');
                if (existingError) existingError.remove();
            }
        }
        
        return hasRating;
    }

    // Validate entire form
    function validateForm(form) {
        const fields = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        // Check rating if present
        if (!validateRating(form)) {
            isValid = false;
        }
        
        return isValid;
    }

    // Initialize validation on a form
    function initFormValidation(form) {
        if (!form) return;
        
        // Add validation on blur for each field
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            // Clear error on input
            field.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    clearError(this);
                }
            });
        });
        
        // Validate on submit
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }

    // Initialize all forms with validation
    document.addEventListener('DOMContentLoaded', function() {
        // Feedback Form
        const feedbackForm = document.getElementById('feedbackForm');
        if (feedbackForm) {
            initFormValidation(feedbackForm);
        }
        
        // Contact Form
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            initFormValidation(contactForm);
        }
        
        // Login Form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            initFormValidation(loginForm);
        }
        
        // Register Form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            initFormValidation(registerForm);
        }
        
        // Admin Forms
        const adminForms = document.querySelectorAll('.admin-form');
        adminForms.forEach(form => {
            initFormValidation(form);
        });
    });

    // Expose functions globally
    window.JifValidation = {
        validateField: validateField,
        validateForm: validateForm,
        showError: showError,
        clearError: clearError,
        initFormValidation: initFormValidation
    };

})();
