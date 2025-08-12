// Phone Creation Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('phoneForm');
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const messageContainer = document.getElementById('messageContainer');

    // Image preview functionality
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                showMessage('File size must be less than 5MB', 'danger');
                imageInput.value = '';
                hideImagePreview();
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                showMessage('Please select a valid image file', 'danger');
                imageInput.value = '';
                hideImagePreview();
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                showImagePreview();
            };
            reader.readAsDataURL(file);
        } else {
            hideImagePreview();
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });

    // Real-time validation
    const inputs = form.querySelectorAll('input[required], textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(input);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(input);
        });
    });
});

// Show image preview
function showImagePreview() {
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.style.display = 'block';
}

// Hide image preview
function hideImagePreview() {
    const imagePreview = document.getElementById('imagePreview');
    imagePreview.style.display = 'none';
}

// Form validation
function validateForm() {
    let isValid = true;
    const requiredFields = ['brand', 'model', 'color', 'price', 'stock'];
    
    // Clear previous messages
    clearMessages();
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!validateField(field)) {
            isValid = false;
        }
    });

    // Validate price
    const price = document.getElementById('price');
    if (price.value && (parseFloat(price.value) < 0 || parseFloat(price.value) > 999999.99)) {
        showFieldError(price, 'Price must be between $0 and $999,999.99');
        isValid = false;
    }

    // Validate stock
    const stock = document.getElementById('stock');
    if (stock.value && (parseInt(stock.value) < 0 || parseInt(stock.value) > 4294967295)) {
        showFieldError(stock, 'Stock must be a positive number');
        isValid = false;
    }

    return isValid;
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, `${getFieldLabel(field)} is required`);
        return false;
    }
    
    // Check max length
    const maxLength = field.getAttribute('maxlength');
    if (maxLength && value.length > parseInt(maxLength)) {
        showFieldError(field, `${getFieldLabel(field)} must be less than ${maxLength} characters`);
        return false;
    }
    
    clearFieldError(field);
    field.classList.add('is-valid');
    return true;
}

// Get field label text
function getFieldLabel(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    return label ? label.textContent.replace('*', '').trim() : field.name;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Clear field error
function clearFieldError(field) {
    field.classList.remove('is-invalid', 'is-valid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Submit form via AJAX
async function submitForm() {
    const form = document.getElementById('phoneForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    // Show loading state
    submitBtn.disabled = true;
    loadingSpinner.classList.remove('d-none');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding Phone...';
    
    try {
        console.log('Submitting form to ../php/phones.php...');
        
        const response = await fetch('../php/phones.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Server response:', result);
        
        if (result.success) {
            showMessage(result.message, 'success');
            resetForm();
            // Scroll to top to show success message
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            showMessage(result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred while adding the phone. Please try again.', 'danger');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        loadingSpinner.classList.add('d-none');
        submitBtn.innerHTML = 'Add Phone';
    }
}

// Show message
function showMessage(message, type) {
    const messageContainer = document.getElementById('messageContainer');
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    messageContainer.appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 150);
        }
    }, 5000);
}

// Clear all messages
function clearMessages() {
    const messageContainer = document.getElementById('messageContainer');
    messageContainer.innerHTML = '';
}

// Reset form
function resetForm() {
    const form = document.getElementById('phoneForm');
    form.reset();
    
    // Clear all validation states
    const fields = form.querySelectorAll('.form-control');
    fields.forEach(field => {
        clearFieldError(field);
    });
    
    // Hide image preview
    hideImagePreview();
    
    // Clear messages
    clearMessages();
}

// Format currency input
document.getElementById('price').addEventListener('input', function(e) {
    let value = e.target.value;
    
    // Remove any non-digit characters except decimal point
    value = value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts[1] && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    e.target.value = value;
});

// Format stock input (integers only)
document.getElementById('stock').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^\d]/g, '');
});

// Prevent form submission on Enter key in text inputs (except textarea)
document.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const nextInput = getNextInput(this);
            if (nextInput) {
                nextInput.focus();
            }
        }
    });
});

// Get next input field
function getNextInput(currentInput) {
    const inputs = Array.from(document.querySelectorAll('input, textarea, button'));
    const currentIndex = inputs.indexOf(currentInput);
    return inputs[currentIndex + 1];
}

// Auto-resize textarea
document.getElementById('description').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// File input styling enhancement
document.getElementById('image').addEventListener('change', function() {
    const fileName = this.files[0]?.name;
    if (fileName) {
        const label = this.parentNode.querySelector('.form-text');
        label.textContent = `Selected: ${fileName}`;
        label.style.color = '#28a745';
    }
});