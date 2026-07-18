// Legal Advisor Website JavaScript

// Global variables
let currentCaseId = null;
let chatPollingInterval = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    initializeTimeSlots();
    initializeChat();
    initializeFormValidation();
});

// Calendar functionality
function initializeCalendar() {
    const calendarElement = document.getElementById('appointment-calendar');
    if (!calendarElement) return;

    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    
    generateCalendar(currentYear, currentMonth);
}

function generateCalendar(year, month) {
    const calendarGrid = document.querySelector('.calendar-grid');
    if (!calendarGrid) return;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Clear existing calendar
    calendarGrid.innerHTML = '';

    // Add day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'calendar-day-header';
        dayHeader.textContent = day;
        dayHeader.style.fontWeight = 'bold';
        dayHeader.style.background = '#f8f9fa';
        calendarGrid.appendChild(dayHeader);
    });

    // Add empty cells for days before month starts
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day disabled';
        calendarGrid.appendChild(emptyDay);
    }

    // Add days of the month
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;
        
        const currentDate = new Date(year, month, day);
        
        // Disable past dates
        if (currentDate < today.setHours(0, 0, 0, 0)) {
            dayElement.classList.add('disabled');
        } else {
            dayElement.addEventListener('click', function() {
                selectDate(year, month, day);
            });
        }
        
        calendarGrid.appendChild(dayElement);
    }
}

function selectDate(year, month, day) {
    // Remove previous selection
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selection to clicked day
    event.target.classList.add('selected');
    
    // Set hidden input value
    const dateInput = document.getElementById('appointment_date');
    if (dateInput) {
        const selectedDate = new Date(year, month, day);
        dateInput.value = selectedDate.toISOString().split('T')[0];
    }
    
    // Show time slots
    const timeSlotsContainer = document.getElementById('time-slots-container');
    if (timeSlotsContainer) {
        timeSlotsContainer.style.display = 'block';
    }
}

// Time slots functionality
function initializeTimeSlots() {
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            selectTimeSlot(this);
        });
    });
}

function selectTimeSlot(selectedSlot) {
    // Remove previous selection
    document.querySelectorAll('.time-slot.selected').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    // Add selection to clicked slot
    selectedSlot.classList.add('selected');
    
    // Set hidden input value
    const timeInput = document.getElementById('appointment_time');
    if (timeInput) {
        timeInput.value = selectedSlot.dataset.time;
    }
}

// Chat functionality
function initializeChat() {
    const chatContainer = document.getElementById('chat-container');
    if (!chatContainer) return;

    const caseId = chatContainer.dataset.caseId;
    if (caseId) {
        currentCaseId = caseId;
        startChatPolling();
        
        // Initialize send message functionality
        const sendButton = document.getElementById('send-message');
        const messageInput = document.getElementById('message-input');
        
        if (sendButton && messageInput) {
            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
    }
}

function startChatPolling() {
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }
    
    // Poll for new messages every 3 seconds
    chatPollingInterval = setInterval(loadChatMessages, 3000);
    
    // Load messages immediately
    loadChatMessages();
}

function loadChatMessages() {
    if (!currentCaseId) return;
    
    fetch(`ajax/load_messages.php?case_id=${currentCaseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChatMessages(data.messages);
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function updateChatMessages(messages) {
    const messagesContainer = document.getElementById('chat-messages');
    if (!messagesContainer) return;
    
    messagesContainer.innerHTML = '';
    
    messages.forEach(message => {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${message.is_own ? 'sent' : 'received'}`;
        
        messageElement.innerHTML = `
            <div class="message-content">${escapeHtml(message.message)}</div>
            <div class="message-time">${message.sender_type}: ${formatDateTime(message.created_at)}</div>
        `;
        
        messagesContainer.appendChild(messageElement);
    });
    
    // Scroll to bottom
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!message || !currentCaseId) return;
    
    const formData = new FormData();
    formData.append('case_id', currentCaseId);
    formData.append('message', message);
    
    fetch('ajax/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadChatMessages(); // Refresh messages
        } else {
            alert('Error sending message: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message. Please try again.');
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Password confirmation
    const passwordField = form.querySelector('input[name="password"]');
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    
    if (passwordField && confirmPasswordField) {
        if (passwordField.value !== confirmPasswordField.value) {
            showFieldError(confirmPasswordField, 'Passwords do not match');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.style.color = '#dc3545';
    errorElement.style.fontSize = '0.875rem';
    errorElement.style.marginTop = '0.25rem';
    errorElement.textContent = message;
    
    field.parentNode.appendChild(errorElement);
    field.style.borderColor = '#dc3545';
}

function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleString();
}

// Rating functionality
function setRating(lawyerId, rating) {
    const stars = document.querySelectorAll(`[data-lawyer-id="${lawyerId}"] .star`);
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('empty');
        } else {
            star.classList.add('empty');
        }
    });
}

function submitRating(lawyerId, caseId) {
    const ratingContainer = document.querySelector(`[data-lawyer-id="${lawyerId}"]`);
    const selectedStars = ratingContainer.querySelectorAll('.star:not(.empty)').length;
    const reviewText = ratingContainer.querySelector('.review-text')?.value || '';
    
    if (selectedStars === 0) {
        alert('Please select a rating');
        return;
    }
    
    const formData = new FormData();
    formData.append('lawyer_id', lawyerId);
    formData.append('case_id', caseId);
    formData.append('rating', selectedStars);
    formData.append('review', reviewText);
    
    fetch('ajax/submit_rating.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Rating submitted successfully!');
            location.reload();
        } else {
            alert('Error submitting rating: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error submitting rating:', error);
        alert('Error submitting rating. Please try again.');
    });
}

// Admin functions
function approveAppointment(appointmentId) {
    updateAppointmentStatus(appointmentId, 'approved');
}

function rejectAppointment(appointmentId) {
    updateAppointmentStatus(appointmentId, 'rejected');
}

function updateAppointmentStatus(appointmentId, status) {
    if (!confirm(`Are you sure you want to ${status} this appointment?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('appointment_id', appointmentId);
    formData.append('status', status);
    
    fetch('ajax/update_appointment_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Appointment ${status} successfully!`);
            location.reload();
        } else {
            alert('Error updating appointment: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating appointment:', error);
        alert('Error updating appointment. Please try again.');
    });
}

function approveLawyer(lawyerId) {
    updateLawyerStatus(lawyerId, 'approved');
}

function rejectLawyer(lawyerId) {
    updateLawyerStatus(lawyerId, 'rejected');
}

function updateLawyerStatus(lawyerId, status) {
    if (!confirm(`Are you sure you want to ${status} this lawyer?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('lawyer_id', lawyerId);
    formData.append('status', status);
    
    fetch('ajax/update_lawyer_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Lawyer ${status} successfully!`);
            location.reload();
        } else {
            alert('Error updating lawyer status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating lawyer status:', error);
        alert('Error updating lawyer status. Please try again.');
    });
}

// Case status update
function updateCaseStatus(caseId, status) {
    const formData = new FormData();
    formData.append('case_id', caseId);
    formData.append('status', status);
    
    fetch('ajax/update_case_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Case status updated successfully!');
            location.reload();
        } else {
            alert('Error updating case status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating case status:', error);
        alert('Error updating case status. Please try again.');
    });
}

// Search functionality
function searchLawyers() {
    const searchTerm = document.getElementById('lawyer-search').value;
    const specialization = document.getElementById('specialization-filter').value;
    
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (specialization) params.append('specialization', specialization);
    
    window.location.href = `lawyers.php?${params.toString()}`;
}

// Auto-refresh for real-time updates
function startAutoRefresh(interval = 30000) {
    setInterval(() => {
        const currentPage = window.location.pathname.split('/').pop();
        if (['dashboard.php', 'appointments.php', 'cases.php'].includes(currentPage)) {
            // Only refresh if user is not actively typing or interacting
            if (!document.activeElement || document.activeElement.tagName !== 'INPUT') {
                location.reload();
            }
        }
    }, interval);
}

// Initialize auto-refresh on dashboard pages
if (window.location.pathname.includes('dashboard') || 
    window.location.pathname.includes('appointments') || 
    window.location.pathname.includes('cases')) {
    startAutoRefresh();
}