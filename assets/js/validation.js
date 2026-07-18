document.addEventListener('DOMContentLoaded', function() {
    // Initialize password toggle functionality
    initPasswordToggle();
    
    // Initialize real-time validation
    initRealTimeValidation();
});

function initPasswordToggle() {
    const passwordFields = document.querySelectorAll('.password-field');
    
    passwordFields.forEach(field => {
        const input = field.querySelector('input');
        const toggle = field.querySelector('.password-toggle');
        
        if (toggle) {
            toggle.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                toggle.innerHTML = isPassword ? '👁️‍🗨️' : '👁️';
                
                // Reset to password type on form submit
                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        input.type = 'password';
                        toggle.innerHTML = '👁️';
                    });
                }
            });
        }
    });
}

function initRealTimeValidation() {
    // Username validation (only letters, numbers, underscore)
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            validateUsername(this);
        });
    }

    // Password validation (8+ chars, upper, lower, number, special)
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this);
            
            // Also validate confirm password when password changes
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword && confirmPassword.value) {
                validateConfirmPassword(confirmPassword);
            }
        });
    }

    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validateConfirmPassword(this);
        });
    }

    // Full name validation (only letters and spaces)
    const fullNameInput = document.getElementById('full_name');
    if (fullNameInput) {
        fullNameInput.addEventListener('input', function() {
            validateFullName(this);
        });
    }

    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });
    }

    // Phone validation (10 digits)
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            validatePhone(this);
        });
    }

    // User type validation (login page)
    const userTypeInput = document.getElementById('user_type');
    if (userTypeInput) {
        userTypeInput.addEventListener('change', function() {
            validateUserType(this);
        });
    }
}

function validateUsername(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('username-error');
    const isValid = /^[a-zA-Z0-9_]+$/.test(value);
    
    if (value === '') {
        showValidation(input, errorElement, false, 'Username is required');
        return false;
    }
    
    if (!isValid) {
        showValidation(input, errorElement, false, 'Only letters, numbers and underscore allowed');
        return false;
    }
    
    showValidation(input, errorElement, true, 'Username is valid');
    return true;
}

function validatePassword(input) {
    const value = input.value;
    const errorElement = document.getElementById('password-error');
    const hasMinLength = value.length >= 8;
    const hasUpper = /[A-Z]/.test(value);
    const hasLower = /[a-z]/.test(value);
    const hasNumber = /[0-9]/.test(value);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
    
    if (value === '') {
        showValidation(input, errorElement, false, 'Password is required');
        return false;
    }
    
    const messages = [];
    if (!hasMinLength) messages.push('Minimum 8 characters');
    if (!hasUpper) messages.push('At least one uppercase letter');
    if (!hasLower) messages.push('At least one lowercase letter');
    if (!hasNumber) messages.push('At least one number');
    if (!hasSpecial) messages.push('At least one special character');
    
    if (messages.length > 0) {
        showValidation(input, errorElement, false, messages.join('<br>'));
        return false;
    }
    
    showValidation(input, errorElement, true, 'Password is strong');
    return true;
}

function validateConfirmPassword(input) {
    const passwordInput = document.getElementById('password');
    const password = passwordInput ? passwordInput.value : '';
    const confirmPassword = input.value;
    const errorElement = document.getElementById('confirm_password-error');
    
    if (confirmPassword === '') {
        showValidation(input, errorElement, false, 'Please confirm your password');
        return false;
    }
    
    if (password !== confirmPassword) {
        showValidation(input, errorElement, false, 'Passwords do not match');
        return false;
    }
    
    showValidation(input, errorElement, true, 'Passwords match');
    return true;
}

function validateFullName(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('full_name-error');
    const isValid = /^[a-zA-Z\s]+$/.test(value);
    
    if (value === '') {
        showValidation(input, errorElement, false, 'Full name is required');
        return false;
    }
    
    if (!isValid) {
        showValidation(input, errorElement, false, 'Only letters and spaces allowed');
        return false;
    }
    
    showValidation(input, errorElement, true, 'Full name is valid');
    return true;
}

function validateEmail(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('email-error');
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    
    if (value === '') {
        showValidation(input, errorElement, false, 'Email is required');
        return false;
    }
    
    if (!isValid) {
        showValidation(input, errorElement, false, 'Please enter a valid email address');
        return false;
    }
    
    showValidation(input, errorElement, true, 'Email is valid');
    return true;
}

function validatePhone(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('phone-error');
    const isValid = /^\d{10}$/.test(value);
    
    if (value === '') {
        showValidation(input, errorElement, true, ''); // Phone is optional
        return true;
    }
    
    if (!isValid) {
        showValidation(input, errorElement, false, 'Please enter a 10-digit phone number');
        return false;
    }
    
    showValidation(input, errorElement, true, 'Phone number is valid');
    return true;
}

function validateUserType(input) {
    const value = input.value;
    const errorElement = document.getElementById('user_type-error');
    
    if (value === '') {
        showValidation(input, errorElement, false, 'Please select a user type');
        return false;
    }
    
    showValidation(input, errorElement, true, '');
    return true;
}

function showValidation(input, errorElement, isValid, message) {
    if (!errorElement) return;
    
    errorElement.innerHTML = message;
    errorElement.style.color = isValid ? '#28a745' : '#dc3545';
    input.style.borderColor = isValid ? '#28a745' : '#dc3545';
    
    // Add/remove checkmark
    const parent = input.parentNode;
    const checkmark = parent.querySelector('.checkmark');
    
    if (isValid && message !== '' && message !== 'Phone is optional') {
        if (!checkmark) {
            const check = document.createElement('span');
            check.className = 'checkmark';
            check.innerHTML = '✓';
            check.style.color = '#28a745';
            check.style.marginLeft = '5px';
            parent.appendChild(check);
        }
    } else if (checkmark) {
        checkmark.remove();
    }
}