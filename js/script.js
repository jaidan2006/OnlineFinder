// Online Tutor and Coaching Center Finder - JavaScript Functions

// Form Validation Functions
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(phone);
}

function validatePassword(password) {
    return password.length >= 6;
}

function validateName(name) {
    return name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name);
}

// Student Registration Validation
function validateStudentRegistration() {
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    let errors = [];
    
    if (!validateName(firstName)) {
        errors.push('Please enter a valid first name');
    }
    
    if (!validateName(lastName)) {
        errors.push('Please enter a valid last name');
    }
    
    if (!validateEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (phone && !validatePhone(phone)) {
        errors.push('Please enter a valid 10-digit phone number');
    }
    
    if (!validatePassword(password)) {
        errors.push('Password must be at least 6 characters long');
    }
    
    if (password !== confirmPassword) {
        errors.push('Passwords do not match');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Tutor Registration Validation
function validateTutorRegistration() {
    const firstName = document.getElementById('first_name').value;
    const lastName = document.getElementById('last_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const qualification = document.getElementById('qualification').value;
    const experience = document.getElementById('experience_years').value;
    const hourlyRate = document.getElementById('hourly_rate').value;
    
    let errors = [];
    
    if (!validateName(firstName)) {
        errors.push('Please enter a valid first name');
    }
    
    if (!validateName(lastName)) {
        errors.push('Please enter a valid last name');
    }
    
    if (!validateEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!validatePhone(phone)) {
        errors.push('Please enter a valid 10-digit phone number');
    }
    
    if (!validatePassword(password)) {
        errors.push('Password must be at least 6 characters long');
    }
    
    if (password !== confirmPassword) {
        errors.push('Passwords do not match');
    }
    
    if (!qualification.trim()) {
        errors.push('Please enter your qualification');
    }
    
    if (!experience || experience < 0) {
        errors.push('Please enter valid years of experience');
    }
    
    if (!hourlyRate || hourlyRate <= 0) {
        errors.push('Please enter a valid hourly rate');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Coaching Center Registration Validation
function validateCenterRegistration() {
    const centerName = document.getElementById('center_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const address = document.getElementById('address').value;
    
    let errors = [];
    
    if (!centerName.trim()) {
        errors.push('Please enter center name');
    }
    
    if (!validateEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!validatePhone(phone)) {
        errors.push('Please enter a valid 10-digit phone number');
    }
    
    if (!validatePassword(password)) {
        errors.push('Password must be at least 6 characters long');
    }
    
    if (password !== confirmPassword) {
        errors.push('Passwords do not match');
    }
    
    if (!address.trim()) {
        errors.push('Please enter center address');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Login Validation
function validateLogin(userType) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    let errors = [];
    
    if (!validateEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!password) {
        errors.push('Please enter your password');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Booking Validation
function validateBooking() {
    const bookingDate = document.getElementById('booking_date').value;
    const bookingTime = document.getElementById('booking_time').value;
    const duration = document.getElementById('duration_hours').value;
    const message = document.getElementById('message').value;
    
    let errors = [];
    
    if (!bookingDate) {
        errors.push('Please select a booking date');
    }
    
    if (!bookingTime) {
        errors.push('Please select a booking time');
    }
    
    if (!duration || duration <= 0) {
        errors.push('Please enter valid duration');
    }
    
    if (!message.trim()) {
        errors.push('Please enter a message for the tutor/center');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Review Validation
function validateReview() {
    const rating = document.querySelector('input[name="rating"]:checked');
    const reviewText = document.getElementById('review_text').value;
    
    let errors = [];
    
    if (!rating) {
        errors.push('Please select a rating');
    }
    
    if (!reviewText.trim()) {
        errors.push('Please write a review');
    }
    
    if (reviewText.trim().length < 10) {
        errors.push('Review must be at least 10 characters long');
    }
    
    if (errors.length > 0) {
        showErrors(errors);
        return false;
    }
    
    return true;
}

// Search Validation
function validateSearch() {
    const subject = document.getElementById('subject').value;
    const location = document.getElementById('location').value;
    const mode = document.getElementById('mode').value;
    
    if (!subject && !location && !mode) {
        showErrors(['Please select at least one search criteria']);
        return false;
    }
    
    return true;
}

// Error Display Function
function showErrors(errors) {
    let errorHtml = '<div class="error-message"><ul>';
    errors.forEach(function(error) {
        errorHtml += '<li>' + error + '</li>';
    });
    errorHtml += '</ul></div>';
    
    // Remove existing error messages
    const existingErrors = document.querySelectorAll('.error-message');
    existingErrors.forEach(function(error) {
        error.remove();
    });
    
    // Add new error message at the top of the form
    const firstForm = document.querySelector('form');
    if (firstForm) {
        firstForm.insertAdjacentHTML('afterbegin', errorHtml);
        
        // Scroll to top of form
        firstForm.scrollIntoView({ behavior: 'smooth' });
    }
}

// Clear Error Messages
function clearErrors() {
    const errors = document.querySelectorAll('.error-message');
    errors.forEach(function(error) {
        error.remove();
    });
}

// Show Loading State
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner"></span> Processing...';
    button.disabled = true;
    button.dataset.originalText = originalText;
}

// Hide Loading State
function hideLoading(button) {
    if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
        button.disabled = false;
        delete button.dataset.originalText;
    }
}

// AJAX Functions
function makeAjaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                callback(xhr.responseText);
            } else {
                showErrors(['An error occurred. Please try again.']);
            }
        }
    };
    
    xhr.send(data);
}

// Check Email Availability
function checkEmailAvailability(email, userType) {
    makeAjaxRequest('includes/check_email.php', 'POST', 
        'email=' + encodeURIComponent(email) + '&user_type=' + userType,
        function(response) {
            const result = JSON.parse(response);
            const emailField = document.getElementById('email');
            const emailError = document.getElementById('email_error');
            
            if (emailError) {
                if (result.exists) {
                    emailError.textContent = 'This email is already registered';
                    emailError.style.color = 'red';
                } else {
                    emailError.textContent = 'Email available';
                    emailError.style.color = 'green';
                }
            }
        }
    );
}

// Rating Stars Interaction
function setupRatingStars() {
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating_value');
    
    stars.forEach(function(star, index) {
        star.addEventListener('click', function() {
            const rating = index + 1;
            if (ratingInput) {
                ratingInput.value = rating;
            }
            
            // Update star display
            stars.forEach(function(s, i) {
                if (i < rating) {
                    s.classList.add('active');
                    s.innerHTML = '★';
                } else {
                    s.classList.remove('active');
                    s.innerHTML = '☆';
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = index + 1;
            stars.forEach(function(s, i) {
                if (i < rating) {
                    s.innerHTML = '★';
                } else {
                    s.innerHTML = '☆';
                }
            });
        });
    });
    
    // Reset stars on mouse leave
    const ratingContainer = document.querySelector('.rating-container');
    if (ratingContainer) {
        ratingContainer.addEventListener('mouseleave', function() {
            const currentRating = ratingInput ? ratingInput.value : 0;
            stars.forEach(function(s, i) {
                if (i < currentRating) {
                    s.innerHTML = '★';
                } else {
                    s.innerHTML = '☆';
                }
            });
        });
    }
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    const strengthIndicator = document.getElementById('password_strength');
    if (strengthIndicator) {
        let strengthText = '';
        let strengthColor = '';
        
        switch(strength) {
            case 0:
            case 1:
                strengthText = 'Weak';
                strengthColor = 'red';
                break;
            case 2:
            case 3:
                strengthText = 'Medium';
                strengthColor = 'orange';
                break;
            case 4:
            case 5:
                strengthText = 'Strong';
                strengthColor = 'green';
                break;
        }
        
        strengthIndicator.textContent = strengthText;
        strengthIndicator.style.color = strengthColor;
    }
}

// Initialize on DOM Load
document.addEventListener('DOMContentLoaded', function() {
    // Setup rating stars if they exist
    setupRatingStars();
    
    // Setup password strength checker
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(function(field) {
        field.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    });
    
    // Setup email availability checker
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            const userType = document.querySelector('input[name="user_type"]')?.value || 'student';
            if (validateEmail(this.value)) {
                checkEmailAvailability(this.value, userType);
            }
        });
    });
    
    // Clear errors on form input
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(function(input) {
        input.addEventListener('input', clearErrors);
        input.addEventListener('change', clearErrors);
    });
    
    // Setup form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                showLoading(submitButton);
            }
        });
    });
});

// Utility Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const time = new Date('2000-01-01 ' + timeString);
    return time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function confirmAction(message) {
    return confirm(message);
}

// Print Function
function printPage() {
    window.print();
}

// Scroll to Top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Show/Hide Password
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleButton = document.getElementById(fieldId + '_toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggleButton.textContent = 'Hide';
    } else {
        field.type = 'password';
        toggleButton.textContent = 'Show';
    }
}
