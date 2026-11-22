document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
            navToggle.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('show');
                navToggle.classList.remove('active');
            }
        });
    }

    const flashMsg = document.getElementById('flashMessage');
    if (flashMsg) {
        setTimeout(function() {
            flashMsg.style.opacity = '0';
            flashMsg.style.transition = 'opacity 0.5s';
            setTimeout(function() { flashMsg.remove(); }, 500);
        }, 5000);
    }
});

function validateRegistrationForm(form) {
    clearErrors(form);
    let isValid = true;
    
    const fullName = form.querySelector('[name="full_name"]');
    const email = form.querySelector('[name="email"]');
    const phone = form.querySelector('[name="phone"]');
    const password = form.querySelector('[name="password"]');
    const confirmPassword = form.querySelector('[name="confirm_password"]');
    
    if (!fullName.value.trim()) {
        showError(fullName, 'Full name is required.');
        isValid = false;
    } else if (fullName.value.trim().length < 2) {
        showError(fullName, 'Name must be at least 2 characters.');
        isValid = false;
    }
    
    if (!email.value.trim()) {
        showError(email, 'Email is required.');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address.');
        isValid = false;
    }
    
    if (!phone.value.trim()) {
        showError(phone, 'Phone number is required.');
        isValid = false;
    } else if (!isValidPhone(phone.value)) {
        showError(phone, 'Please enter a valid phone number (10 digits).');
        isValid = false;
    }
    
    if (!password.value) {
        showError(password, 'Password is required.');
        isValid = false;
    } else if (password.value.length < 6) {
        showError(password, 'Password must be at least 6 characters.');
        isValid = false;
    }
    
    if (!confirmPassword.value) {
        showError(confirmPassword, 'Please confirm your password.');
        isValid = false;
    } else if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match.');
        isValid = false;
    }
    
    return isValid;
}

function validateLoginForm(form) {
    clearErrors(form);
    let isValid = true;
    
    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    
    if (!email.value.trim()) {
        showError(email, 'Email is required.');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address.');
        isValid = false;
    }
    
    if (!password.value) {
        showError(password, 'Password is required.');
        isValid = false;
    }
    
    return isValid;
}

function validateBookingForm(form) {
    clearErrors(form);
    let isValid = true;
    
    const startDate = form.querySelector('[name="start_date"]');
    const endDate = form.querySelector('[name="end_date"]');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (!startDate.value) {
        showError(startDate, 'Start date is required.');
        isValid = false;
    } else if (new Date(startDate.value) < today) {
        showError(startDate, 'Start date cannot be in the past.');
        isValid = false;
    }
    
    if (!endDate.value) {
        showError(endDate, 'End date is required.');
        isValid = false;
    } else if (startDate.value && new Date(endDate.value) <= new Date(startDate.value)) {
        showError(endDate, 'End date must be after start date.');
        isValid = false;
    }
    
    return isValid;
}

function calculateRentalPrice() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const pricePerDay = parseFloat(document.getElementById('price_per_day')?.value || 0);
    const rentalDaysEl = document.getElementById('rental_days');
    const totalAmountEl = document.getElementById('total_amount');
    const totalDisplayEl = document.getElementById('total_display');
    
    if (startDate && endDate && startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            const total = diffDays * pricePerDay;
            if (rentalDaysEl) rentalDaysEl.value = diffDays;
            if (totalAmountEl) totalAmountEl.value = total.toFixed(2);
            if (totalDisplayEl) totalDisplayEl.textContent = 'Rs. ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        } else {
            if (rentalDaysEl) rentalDaysEl.value = 0;
            if (totalAmountEl) totalAmountEl.value = '0.00';
            if (totalDisplayEl) totalDisplayEl.textContent = 'Rs. 0.00';
        }
    }
}

function validateContactForm(form) {
    clearErrors(form);
    let isValid = true;
    
    const name = form.querySelector('[name="name"]');
    const email = form.querySelector('[name="email"]');
    const subject = form.querySelector('[name="subject"]');
    const message = form.querySelector('[name="message"]');
    
    if (!name.value.trim()) {
        showError(name, 'Name is required.');
        isValid = false;
    }
    if (!email.value.trim()) {
        showError(email, 'Email is required.');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email.');
        isValid = false;
    }
    if (!subject.value.trim()) {
        showError(subject, 'Subject is required.');
        isValid = false;
    }
    if (!message.value.trim()) {
        showError(message, 'Message is required.');
        isValid = false;
    }
    
    return isValid;
}

function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
}

function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    return /^[0-9]{10}$/.test(phone.replace(/[\s\-]/g, ''));
}

function showError(input, message) {
    input.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.form-error').forEach(el => el.remove());
}

function switchPaymentTab(method) {

    document.querySelectorAll('.payment-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    const activeTab = document.querySelector('[data-method="' + method + '"]');
    if (activeTab) activeTab.classList.add('active');

    document.querySelectorAll('.payment-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    const activePane = document.getElementById('pane-' + method);
    if (activePane) activePane.classList.add('active');
}

function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
