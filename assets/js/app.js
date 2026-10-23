/**
 * SkillBridge — Client-Side JavaScript
 * 
 * Handles: navigation toggle, form validation, AJAX helpers,
 * flash message dismissal, tabs, skill chips, and utility functions.
 */

// ============================================================
// DOM Ready
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    initNavbar();
    initAlertDismiss();
    initTabs();
    initFormValidation();
    initSkillChips();
    initLiveExplorerFilter();
});

// ============================================================
// Live Client-side Explorer Filtering
// ============================================================
function initLiveExplorerFilter() {
    var searchInput = document.querySelector('.filters-bar input[name="search"]');
    var cards = document.querySelectorAll('.internship-card');
    if (searchInput && cards.length > 0) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            cards.forEach(function(card) {
                var text = card.textContent.toLowerCase();
                if (!q || text.indexOf(q) !== -1) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

// ============================================================
// Mobile Navbar Toggle
// ============================================================
function initNavbar() {
    const toggle = document.querySelector('.navbar-toggle');
    const nav = document.querySelector('.navbar-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('open');
        });
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
            }
        });
    }
}

// ============================================================
// Alert / Flash Message Dismiss
// ============================================================
function initAlertDismiss() {
    document.querySelectorAll('.alert-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('.alert').style.display = 'none';
        });
    });
}

// ============================================================
// Tab System
// ============================================================
function initTabs() {
    document.querySelectorAll('.tabs').forEach(function(tabGroup) {
        const tabs = tabGroup.querySelectorAll('.tab');
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                const target = this.dataset.tab;
                // Deactivate all tabs in this group
                tabs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
                // Show corresponding content
                const parent = tabGroup.parentElement;
                parent.querySelectorAll('.tab-content').forEach(function(tc) {
                    tc.classList.remove('active');
                });
                const content = parent.querySelector('#' + target);
                if (content) content.classList.add('active');
            });
        });
    });
}

// ============================================================
// Form Validation (client-side — supplement, not replace server)
// ============================================================
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
        // Real-time clearing of error border on input
        form.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.addEventListener('input', function() {
                clearFieldError(this);
            });
            field.addEventListener('change', function() {
                clearFieldError(this);
            });
        });

        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Required fields
            form.querySelectorAll('[required]').forEach(function(field) {
                clearFieldError(field);
                if (!field.value.trim()) {
                    showFieldError(field, 'This field is required.');
                    valid = false;
                }
            });
            
            // Email validation
            form.querySelectorAll('[type="email"]').forEach(function(field) {
                if (field.value && !isValidEmail(field.value)) {
                    showFieldError(field, 'Please enter a valid email address.');
                    valid = false;
                }
            });
            
            // Password confirmation
            var pwd = form.querySelector('#password');
            var confirm = form.querySelector('#confirm_password');
            if (pwd && confirm && pwd.value !== confirm.value) {
                showFieldError(confirm, 'Passwords do not match.');
                valid = false;
            }
            
            // Password strength
            if (pwd && pwd.value) {
                var strength = validatePassword(pwd.value);
                if (strength.length > 0) {
                    showFieldError(pwd, strength[0]);
                    valid = false;
                }
            }
            
            if (!valid) {
                e.preventDefault();
                // Scroll to first error
                var firstErr = form.querySelector('.field-error');
                if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
}

function showFieldError(field, message) {
    field.classList.add('field-error');
    field.style.borderColor = '#ef4444';
    var existing = field.parentElement.querySelector('.error-message');
    if (!existing) {
        var msg = document.createElement('div');
        msg.className = 'error-message';
        msg.style.color = '#ef4444';
        msg.style.fontSize = '0.8rem';
        msg.style.marginTop = '0.25rem';
        msg.textContent = message;
        field.parentElement.appendChild(msg);
    }
}

function clearFieldError(field) {
    field.classList.remove('field-error');
    field.style.borderColor = '';
    var msg = field.parentElement.querySelector('.error-message');
    if (msg) msg.remove();
}

// ============================================================
// Validation Helpers
// ============================================================
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePassword(password) {
    var errors = [];
    if (password.length < 8) errors.push('Password must be at least 8 characters.');
    if (!/[A-Z]/.test(password)) errors.push('Must contain an uppercase letter.');
    if (!/[a-z]/.test(password)) errors.push('Must contain a lowercase letter.');
    if (!/[0-9]/.test(password)) errors.push('Must contain a digit.');
    return errors;
}

// ============================================================
// Practical 4: Number to Words (0-999)
// ============================================================
function numberToWords(num) {
    if (num < 0 || num > 999 || !Number.isInteger(num)) {
        return 'Please enter an integer between 0 and 999.';
    }
    
    var ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven',
                'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
                'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    var tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
                'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    if (num === 0) return 'Zero';
    
    var words = '';
    
    if (num >= 100) {
        words += ones[Math.floor(num / 100)] + ' Hundred';
        num = num % 100;
        if (num > 0) words += ' and ';
    }
    
    if (num >= 20) {
        words += tens[Math.floor(num / 10)];
        num = num % 10;
        if (num > 0) words += ' ' + ones[num];
    } else if (num > 0) {
        words += ones[num];
    }
    
    return words.trim();
}

// ============================================================
// Practical 5: Regex Validation Functions
// ============================================================
var regexPatterns = {
    email:   /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    phone:   /^[6-9]\d{9}$/,
    name:    /^[a-zA-Z\s]{2,100}$/,
    url:     /^https?:\/\/[^\s/$.?#].[^\s]*$/i,
    year:    /^(19|20)\d{2}$/,
    pincode: /^\d{6}$/
};

function validateField(value, type) {
    if (!regexPatterns[type]) return { valid: false, message: 'Unknown validation type.' };
    var valid = regexPatterns[type].test(value);
    var messages = {
        email:   'Enter a valid email (e.g., user@example.com)',
        phone:   'Enter a valid 10-digit Indian phone number',
        name:    'Enter a valid name (letters and spaces only, 2-100 chars)',
        url:     'Enter a valid URL starting with http:// or https://',
        year:    'Enter a valid year (1900-2099)',
        pincode: 'Enter a valid 6-digit pincode'
    };
    return { valid: valid, message: valid ? 'Valid' : messages[type] };
}

// ============================================================
// Skill Chip Selection
// ============================================================
function initSkillChips() {
    document.querySelectorAll('.skill-chip[data-skill-id]').forEach(function(chip) {
        chip.addEventListener('click', function() {
            this.classList.toggle('selected');
            var checkbox = this.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = this.classList.contains('selected');
        });
    });
}

// ============================================================
// AJAX Helper
// ============================================================
function ajaxPost(url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var response = JSON.parse(xhr.responseText);
                callback(null, response);
            } catch(e) {
                callback('Invalid response from server');
            }
        }
    };
    xhr.onerror = function() {
        callback('Network error');
    };
    
    // Convert data object to URL-encoded string
    var encoded = Object.keys(data).map(function(key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
    }).join('&');
    
    xhr.send(encoded);
}

// ============================================================
// Confirm Delete
// ============================================================
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// ============================================================
// Toggle Save Internship
// ============================================================
function toggleSaveInternship(internshipId, csrfToken) {
    ajaxPost('/wtproject/api/save-internship.php', {
        internship_id: internshipId,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) { alert('Error: ' + err); return; }
        var btn = document.querySelector('[data-save-id="' + internshipId + '"]');
        if (btn) {
            if (response.saved) {
                btn.innerHTML = '&#9829; Saved';
                btn.classList.add('btn-primary');
                btn.classList.remove('btn-outline');
            } else {
                btn.innerHTML = '&#9825; Save';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
            }
        }
    });
}

// ============================================================
// Update Learning Task Status
// ============================================================
function updateLearningProgress(taskId, status, csrfToken) {
    ajaxPost('/wtproject/api/learning-progress.php', {
        task_id: taskId,
        status: status,
        csrf_token: csrfToken
    }, function(err, response) {
        if (err) { alert('Error: ' + err); return; }
        if (response.success) {
            location.reload();
        }
    });
}

